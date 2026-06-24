<template>
  <k-panel-inside>
    <k-view>
      <k-header>
        {{ getNestedValue(recordData[0], titleSelector) }}
        <k-button-group slot="buttons">
          <k-view-button
            icon="question"
            text="Helpdocs"
            link="https://heron.helpdocsite.com/erfgoedparels"
            target="_blank"
            theme="blue"
          />
          <language-dropdown-button />
          <k-button variant="filled" icon="check" v-if="canEdit" :theme="isModified ? 'green' : 'gray'" size="sm"
            :disabled="isSaving || !isModified" @click="save">{{ $t('libis.solis.save') }}</k-button>
          <k-button :disabled="isSaving" variant="filled" icon="trash" v-if="canDelete" :theme="'red'" size="sm" @click="openDelete">{{ $t('libis.solis.delete') }}</k-button>
          <k-view-button
            v-if="mainInfo['linkInFrontEnd'] == true && mainInfo['linkFrontEnd'] != ''"
            icon="open"
            :link="'https://' + mainInfo['linkFrontEnd'] + recordData?.[0]?.id"
            target="_blank"
          />
        </k-button-group>
      </k-header>
      <k-tabs tab="record" :tabs="tabsWithId" />
      <div class="page-view">
        <div v-if="isSaving" class="processing-view">
          <div class="loader-component">
            Data wordt verwerkt...
          </div>
        </div>
        <div v-if="amountOfColumns == 2" class="main-info k-grid k-sections" data-variant="columns">
          <div class="main-info__left-main-info left-main-info k-column" style="--width: 2/3;">
            <div class="k-fieldset">
              <div data-variant="fields" class="k-grid">
                <component v-for="field in leftFields" :disabled="field.componentsOptions?.disabled || !canEdit" :key="field.componentsOptions?.options?.length || field.name"
                  :is="field.type === 'entity' ? `k-${field.subType}` : `k-${field.type}`" :name="field.name"
                  :label="field.label" :icon="field.icon" :style="field.style" :required="field.min === 1" v-model="formData[field.name]"
                  v-bind="{ ...field.componentsOptions, apiEndpoints: undefined, linkValue: field.url }" :selected="formData[field.name]"
                  @update:selected="formData[field.name] = $event" v-if="shouldShow(field)" :delete="canDelete" />
              </div>
            </div>
          </div>
          <div class="main-info__right-main-info right-main-info k-column" style="--width: 1/3;">
            <div class="k-fieldset">
              <div data-variant="fields" class="k-grid">
                <component v-for="field in rightFields" :disabled="field.componentsOptions?.disabled || !canEdit" :key="field.componentsOptions?.options?.length || field.name"
                  :is="field.type === 'entity' ? `k-${field.subType}` : `k-${field.type}`" :name="field.name"
                  :label="field.label" :icon="field.icon" :style="field.style" :required="field.min === 1" v-model="formData[field.name]"
                  v-bind="{ ...field.componentsOptions, apiEndpoints: undefined, linkValue: field.url }" :selected="formData[field.name]"
                  @update:selected="formData[field.name] = $event" v-if="shouldShow(field)" :delete="canDelete" />
              </div>
            </div>
          </div>
        </div>
        <div v-else class="main-info k-grid k-sections">
          <div class="k-fieldset" style="--width: 1/1;">
            <div data-variant="fields" class="k-grid">
              <component v-for="field in fields" :disabled="field.componentsOptions?.disabled || !canEdit" :key="field.componentsOptions?.options?.length || field.name"
                :is="field.type === 'entity' ? `k-${field.subType}` : `k-${field.type}`" :name="field.name"
                :label="field.label" :icon="field.icon" :style="field.style" :required="field.min === 1" v-model="formData[field.name]"
                v-bind="{ ...field.componentsOptions, apiEndpoints: undefined, linkValue: field.url }" :selected="formData[field.name]"
                @update:selected="formData[field.name] = $event" v-if="shouldShow(field)" :delete="canDelete" />
            </div>
          </div>
        </div>
      </div>
    </k-view>
  </k-panel-inside>
</template>

<script>
import { buildStructure } from '../../../functions/fieldStructure';
import { fetchApiOptions } from '../../../functions/selectOptions';
import { validateData, formatData } from '../../../functions/processData';
import { getChangedFields } from '../../../functions/getChangedFields';

export default {
  name: "RecordView",
  props: {
    recordData: Array,
    mainInfo: Array,
    titleSelector: String,
    tabs: Array,
    amountOfColumns: Number,
    fields: Array,
    role: {
      type: String,
      default: 'reader',
    }
  },
  data() {
    return {
      originalData: null,
      currentCode: window.panel.language.code,
      formData: {},
      isSaving: false
    };
  },
  async created() {
    for (const field of this.fields) {
      if (field.componentsOptions?.fields) {
        const structures = await buildStructure(field.componentsOptions.fields, this.currentCode);
        field.componentsOptions.fields = structures;
      }
      else if (field.componentsOptions?.apiEndpoint) {
        const options = await fetchApiOptions(
          field.componentsOptions.apiEndpoint,
          this.currentCode,
          field.componentsOptions.textValue,
          field.componentsOptions.valueValue
        );

        this.$set(field.componentsOptions, 'options', options)
      }
    }

    this.formData = this.getCurrentData();
    this.originalData = JSON.parse(JSON.stringify(this.formData));
  },
  computed: {
    canEdit() {
      const edit = ['admin', 'supervisor', 'editor'];
      if(edit.includes(this.role.toLowerCase())) {
        return true;
      }
      else {
        return false;
      }
    },
    canDelete() {
      const edit = ['admin', 'supervisor'];
      if(edit.includes(this.role.toLowerCase())) {
        return true;
      }
      else {
        return false;
      }
    },
    tabsWithId() {
      const id = this.recordData?.[0]?.id || '';
      return this.tabs.map(tab => ({
        ...tab,
        link: tab.link.replace('{id}', this.getLastSegment(id))
      }));
    },
    mappedData() {
      //make an array of all the data linked to its corresponding field
      return this.fields.reduce((allMappedData, field) => {
        // complex fields (needs more functions)
        if ((field.type === 'entity' || field.type == 'select-field') && field.dataFields) {
          //take the data that represents that complex field
          const source = this.recordData?.[0]?.[field['solis-selector']] ?? [];
          // look if field can be added more then once
          const isSingle = field.max || field.componentsOptions?.max === 1 || field.type == "select-field";
          // get all the subfields back (correct structure)
          const subTypes = this.getSubFieldTypes(field);
          //make always an array
          const items = isSingle ? [source] : (Array.isArray(source) ? source : [source]);
          //map all the objects of a type (ex. multiple items) to one array in the correct structure (based on how the field it expect) 
          allMappedData[field.name] = items.map(item => this.mapFields(item, field.dataFields, subTypes, field));
          if (field.type == 'select-field') {
            allMappedData[field.name] = allMappedData[field.name][0].value;
          }
        }
        // simple field like string => just add the data
        else {
          allMappedData[field.name] = this.recordData?.[0]?.[field['solis-selector']] ?? '';
        }
        return allMappedData;
      }, {});
    },
    leftFields() {
      return this.fields.filter(field => field.align === 'left');
    },
    rightFields() {
      return this.fields.filter(field => field.align === 'right');
    },
    isModified() {
      const isChanged = JSON.stringify(this.formData) !== JSON.stringify(this.originalData);

      return isChanged;
    }
  },
  methods: {
    shouldShow(field) {
      if (!field.when) return true;

      if (typeof field.when !== 'string') return true;

      const [targetField, expectedValue] = field.when.split('.');
      return this.formData[targetField] === expectedValue;
    },
    // get all the types of the fields in correct structure
    getSubFieldTypes(field) {
      return Object.fromEntries(
        Object.entries(field.componentsOptions?.fields ?? {}).map(([key, value]) => [key, value.type])
      );
    },

    mapFields(source, dataFields, subTypes, field) {
      const mappedFields = Object.entries(dataFields).reduce((obj, [key, path]) => {
        if (path == "") obj[key] = source;
        else if (typeof path === 'string') obj[key] = this.getNestedValue(source, path) ?? '';
        else if (Array.isArray(path)) obj[key] = path.map(p => this.getNestedValue(source, p)).find(Boolean) ?? '';
        else if (typeof path === 'object') {
          const mustArray = subTypes[key] === 'add_multiple_values_field';
          const data = obj[key] ? this.getNestedValue(source, obj[key]) : source;
          const mapped = Array.isArray(data) ? data.map(d => this.mapFields(d, path, {})) : [this.mapFields(data ?? {}, path, {})];
          obj[key] = mustArray ? mapped : mapped[0];
        }
        return obj;
      }, {});
      if(field && field.url && field.url != null && field.url != "") {
        mappedFields.link = this.mapUrl(source, field.url);
        mappedFields.target = "_blank";
      }
      return mappedFields;
    },
    //when a path has multiple layers to go through splited by a . go to the bottom level
    getNestedValue(obj, path) {
      return path.split('.').reduce((item, path) => item?.[path], obj);
    },
    //when we only need the last part of a link then take it
    getLastSegment(url) {
      return url.split('/').filter(Boolean).pop();
    },
    getCurrentData() {
      const currentData = {};
      this.fields.forEach(field => {
        currentData[field.name] = this.mappedData[field.name];
      });
      return currentData;
    },
    mapUrl(source, url) {
      return url.replace(/{(.*?)}/g, (match, key) => this.getNestedValue(source, key) || '');
    },
    async save() {
      if(this.canEdit == false) return;
      this.isSaving = true;
      const visibleFields = this.fields.filter(field => this.shouldShow(field));
      const validationResult = validateData(this.formData, visibleFields, this.$t);
      if (validationResult !== true) {
        this.isSaving = false;
        this.$panel.dialog.open({
          component: 'k-error-dialog',
          props: {
            submitButton: false,
            message: this.$t('libis.solis.from.errors.title'),
            details: validationResult,
          },
          on: {
            cancel: () => this.$panel.dialog.close(),
            close: () => this.$panel.dialog.close(),
          }
        });
      }
      else {
        const changedData = getChangedFields(this.originalData, this.formData);
        let formattedChangedFields = formatData(changedData, visibleFields);

        formattedChangedFields = Object.fromEntries(
          Object.entries(formattedChangedFields).filter(([key, field]) => field !== undefined && field !== '')
        );

        try {
          const response = await fetch(`${this.mainInfo.requestsInKirbyLink}/${this.recordData[0].id}/${this.currentCode}`, {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(formattedChangedFields)
          });

          const result = await response.json();
          if (result.status === 'success') {
            window.location.reload();
          }
          else {
            this.isSaving = false;
            this.$panel.error(result.message);
          }
        }
        catch (error) {
          this.$panel.error(error);
        }
      }
    },
    openDelete() {
      if(this.canDelete == false) return;
      this.$panel.dialog.open({
        component: 'k-form-dialog',
        props: {
          submitButton: {
            text: this.$t("libis.solis.delete"),
            icon: "trash",
            theme: "red"
          },
          fields: {
            info: {
              label: false,
              type: "info",
              text: '<h3>' + this.$t("libis.solis.delete.message.title", {title: this.getNestedValue(this.recordData[0], this.titleSelector)}) + '</h3>' + 
                    '<p style="margin-top:5px;">' + this.$t("libis.solis.delete.message.text") + '</p>',
              theme: false,
            },
            check: {
              label: this.$t("libis.solis.delete.check.title"),
              type: 'text',
            },
          },
        },
        on: {
          cancel: () => this.$panel.dialog.close(),
          close: () => this.$panel.dialog.close(),
          submit: (data) => {
            if (data.check.trim().toLowerCase() === this.getNestedValue(this.recordData[0], this.titleSelector).trim().toLowerCase()) {
              this.deleteRecord();
            }
            else {

            }
          }
        }
      })
    },
    async deleteRecord() {
      if(this.canDelete == false) return;
      this.$panel.dialog.close();
      this.isSaving = true;
      try {
        const response = await fetch(`${this.mainInfo.requestsInKirbyLink}/${this.recordData[0].id}`, { method: 'DELETE' });

        const result = await response.json();
        if (result.status === 'success') {
          this.$panel.redirect('/records/' + this.mainInfo.type);
        }
        else {
          this.isSaving = false;
          this.$panel.error(result.message);
        }
      }
      catch (error) {
        this.$panel.error(error);
      }
    }
  }
};
</script>

<style>
.processing-view {
  position: fixed;
  inset: 0px;
  background-color: rgba(0,0,0,0.3);
  backdrop-filter: blur(6px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 99999;
  pointer-events: auto;
}

.loader-component {
  padding: 80px 40px;
  background-color: #edeaea;
  color: black;
  font-weight: 500;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 20px;
  border-radius: 10px;
  font-size: 25px;
}

.loader-component::before {
  content: '⏳';
  font-size: 2rem;
  display: block;
  margin-top: .5rem;
}
</style>
