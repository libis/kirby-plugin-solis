<template>
  <k-panel-inside>
    <k-view>
      <k-header>
        {{ capitalizedFirstLetter(mainInfo.singularType) }} {{ $t('libis.solis.add') }}
        <k-button-group slot="buttons">
          <k-view-button
            icon="question"
            text="Helpdocs"
            link="https://heron.helpdocsite.com/erfgoedparels"
            target="_blank"
            theme="blue"
          />
          <k-button variant="filled" icon="check" :theme="isModified ? 'green' : 'gray'" size="sm"
            :disabled="isSaving || !isModified" @click="save">{{ $t('libis.solis.save') }}</k-button>
        </k-button-group>
      </k-header>
      <k-tabs tab="add" :tabs="tabs" />
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
                <component v-for="field in leftFields" :key="field.componentsOptions?.options?.length || field.name"
                  :is="field.type === 'entity' ? `k-${field.subType}` : `k-${field.type}`" :name="field.name"
                  :label="field.label" :icon="field.icon" :style="field.style" :required="field.min === 1" v-model="formData[field.name]"
                  v-bind="{ ...field.componentsOptions, apiEndpoints: undefined, linkValue: field.url }" :selected="formData[field.name]"
                  @update:selected="formData[field.name] = $event" v-if="!field.when || checkCondition(field.when)" />
              </div>
            </div>
          </div>
          <div class="main-info__right-main-info right-main-info k-column" style="--width: 1/3;">
            <div class="k-fieldset">
              <div data-variant="fields" class="k-grid">
                <component v-for="field in rightFields" :key="field.componentsOptions?.options?.length || field.name"
                  :is="field.type === 'entity' ? `k-${field.subType}` : `k-${field.type}`" :name="field.name"
                  :label="field.label" :icon="field.icon" :style="field.style" :required="field.min === 1" v-model="formData[field.name]"
                  v-bind="{ ...field.componentsOptions, apiEndpoints: undefined, linkValue: field.url }" :selected="formData[field.name]"
                  @update:selected="formData[field.name] = $event" v-if="shouldShow(field)" />
              </div>
            </div>
          </div>
        </div>
        <div v-else class="main-info k-grid k-sections">
          <div class="k-fieldset" style="--width: 1/1;">
            <div data-variant="fields" class="k-grid">
              <component v-for="field in fields" :key="field.componentsOptions?.options?.length || field.name"
                :is="field.type === 'entity' ? `k-${field.subType}` : `k-${field.type}`" :name="field.name"
                :label="field.label" :icon="field.icon" :style="field.style" :required="field.min === 1" v-model="formData[field.name]"
                v-bind="{ ...field.componentsOptions, apiEndpoints: undefined, linkValue: field.url }" :selected="formData[field.name]"
                @update:selected="formData[field.name] = $event" v-if="shouldShow(field)" />
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

export default {
  name: "AddRecordView",
  props: {
    mainInfo: Array,
    tabs: Array,
    amountOfColumns: Number,
    fields: Array,
  },
  data() {
    return {
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

    this.formData = this.fields.reduce((acc, field) => {
      if (field.type === 'entity') {
        acc[field.name] = [];
      }
      else if (field.type === 'toggle-field') {
        acc[field.name] = false;
      }
      else {
        acc[field.name] = '';
      }
      return acc;
    }, {});
  },
  computed: {
    mappedData() {
      //make an array of all the data linked to its corresponding field
      return this.fields.reduce((allMappedData, field) => {
        // complex fields (needs more functions)
        if (field.type === 'entity' && field.dataFields) {
          //take the data that represents that complex field
          const source = this.recordData?.[0]?.[field['solis-selector']] ?? [];
          // look if field can be added more then once
          const isSingle = field.max && field.componentOptions?.max === 1;
          // get all the subfields back (correct structure)
          const subTypes = this.getSubFieldTypes(field);
          //make always an array
          const items = isSingle ? { source } : (Array.isArray(source) ? source : [source]);
          //map all the objects of a type (ex. multiple items) to one array in the correct structure (based on how the field it expect) 
          allMappedData[field.name] = items.map(item => this.mapFields(item, field.dataFields, subTypes));
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
      let allChanged = true;
      const visibleFields = this.fields.filter(field => this.shouldShow(field));

      visibleFields.forEach(field => {
        const value = this.formData[field.name];

        // Check verplichte velden
        if (field.min === 1 && (value === null || value === '' || value === undefined)) {
          allChanged = false;
        }

        // Min/Max check voor arrays
        if (field.componentsOptions?.min) {
          const min = field.componentsOptions.min;
          const max = field.componentsOptions.max || null;

          if (Array.isArray(value)) {
            const length = value.length;
            if (length < min) allChanged = false;
            else if (max && length > max) allChanged = false;
          }
        }
      });

      return allChanged;
    }
  },
  methods: {
    shouldShow(field) {
      if (!field.when) return true;

      if (typeof field.when !== 'string') return true;

      const [targetField, expectedValue] = field.when.split('.');
      return this.formData[targetField] == expectedValue;
    },
    // get all the types of the fields
    getSubFieldTypes(field) {
      return Object.fromEntries(
        Object.entries(field.componentsOptions?.fields ?? {}).map(([key, value]) => [key, value.type])
      );
    },
    mapFields(source, dataFields, subTypes) {
      return Object.entries(dataFields).reduce((obj, [key, path]) => {
        if (typeof path === 'string') obj[key] = this.getNestedValue(source, path) ?? '';
        else if (Array.isArray(path)) obj[key] = path.map(p => this.getNestedValue(source, p)).find(Boolean) ?? '';
        else if (typeof path === 'object') {
          const mustArray = subTypes[key] === 'add_multiple_values_field';
          const data = obj[key] ? this.getNestedValue(source, obj[key]) : source;
          const mapped = Array.isArray(data) ? data.map(d => this.mapFields(d, path, {})) : [this.mapFields(data ?? {}, path, {})];
          obj[key] = mustArray ? mapped : mapped[0];
        }
        return obj;
      }, {});
    },
    //when a path has multiple layers to go through splited by a . go to the bottom level
    getNestedValue(obj, path) {
      return path.split('.').reduce((item, path) => item?.[path], obj);
    },
    capitalizedFirstLetter(word) {
      return word.charAt(0).toUpperCase() + word.slice(1)
    },
    getCurrentData() {
      const currentData = {};
      this.fields.forEach(field => {
        currentData[field.name] = this.mappedData[field.name];
      });
      return currentData;
    },
    async save() {
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
        let formatedData = formatData(this.formData, visibleFields);

        try {
          const response = await fetch(this.mainInfo.requestsInKirbyLink, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(formatedData)
          });

          const result = await response.json();

          if (result.status === 'success') {
            this.$panel.redirect(this.mainInfo.requestsInKirbyLink + '/' + result.result.data[0].id);
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