<template>
  <k-field>
    <div v-if="isSaving" class="processing-view">
      <div class="loader-component">
        Data wordt verwerkt...
      </div>
    </div>
    <k-button variant="default" icon="plus" @click="openDrawer()" size="xs">
      {{ $t("libis.solis.add") }}
    </k-button>
  </k-field>
</template>

<script>
import { buildStructure } from '../../functions/fieldStructure';
import { fetchApiOptions } from '../../functions/selectOptions';
import { formatData, validateData } from '../../functions/processData';
export default {
  name: "multiple-values-field",
  props: {
    createdType: String,
    label: String,
    createUrl: String
  },
  data() {
    return {
      data: [],
      currentCode: window.panel.language.code,
      isSaving: false,
      fields: null,
    };
  },
  async mounted() {
    await this.loadFields();
  },
  computed: {
    // based on the fields a record has create the drawer fields for edit and add
    async drawerFields() {
      if (!this.fields) return {};

      const entries = await Promise.all(
        this.fields.map(async (field) => {
          let options;

          if (field.componentsOptions?.fields) {
            options = await buildStructure(
              field.componentsOptions.fields,
              this.currentCode
            );
          } else if (field.componentsOptions?.apiEndpoint) {
            options = await fetchApiOptions(
              field.componentsOptions.apiEndpoint,
              this.currentCode,
              field.componentsOptions.textValue,
              field.componentsOptions.valueValue
            );
          }

          const {
            name,
            subType,
            style,
            componentsOptions = {},
            ...rest
          } = field;

          const normalizedField = {
            ...rest,
            ...componentsOptions,
            name,
            type: field.type === 'entity' ? subType.replaceAll('-', '_') : field.type,
            ...(options && {
              options,
              ...(componentsOptions.fields && { fields: options })
            })
          };

          delete normalizedField.apiEndpoint;

          return [name, normalizedField];
        })
      );

      return Object.fromEntries(
        Object.entries(Object.fromEntries(entries)).map(([key, field]) => [
          key,
          {
            ...field,
            name: field.name ?? key,
            ...(field.type === "add_multiple_values_field" && {
              onChangeRecord: () => {}
            })
          }
        ])
      );
    }
  },
  watch: {
    value: {
      // check if the value is updated (check deep)
      handler(newVal) {
        this.data = Array.isArray(newVal) ? [...newVal] : { ...newVal };
      },
      deep: true
    }
  },
  methods: {
    //get the correct fields of the created item
    async loadFields() {
      try {
        let url = `/create-fields-record?file=${this.createdType}`;

        const response = await fetch(url);
        const result = await response.json();

        if(result.status == 'success') {
          this.fields = result.fields;
        }
      } 
      catch (error) {
        console.error("Unable to load fields", error);
      }
    },
    // if we add a new record we need some default values to add the new stuff to
    async emptyValue() {
      const fields = await this.drawerFields;

      return Object.fromEntries(
        Object.entries(fields).map(([key, field]) => {
          if (
            field.type === "relation_field" ||
            field.type === "add_multiple_values_field"
          ) {
            return [key, []];
          }

          return [key, ""];
        })
      );
    },
    // when item is clicked to edit or to be created open the drawer and give the values with the fields so the drawer show correct data
    async openDrawer() {
      const value = await this.emptyValue();

      this.$panel.drawer.open({
        component: "k-form-drawer",
        props: {
          title: this.label,
          fields: await this.drawerFields,
          value,
          options: [{ icon: "cancel", text: this.$t('libis.solis.cancel'), click: () => this.$panel.drawer.close() }]
        },
        on: {
          submit: data => this.updateData(data),
          input:
            updated => {
              for (const key in updated) {
                const newVal = updated[key];

                if (Array.isArray(newVal)) {
                  this.$set(value, key, [...newVal]);
                }
                else {
                  this.$set(value, key, newVal);
                }
              }
            }
        }
      });
    },
    // if the user create the record the data will be validated and if correct there will be an api call to the backend to check if the record already excisted if not check a second time and send it to the database
    // if already excisted ask permission for adding that record to the list
    // if create is ok send oke to parent otherwise show the error message
    async updateData(data) {
      const normalizedFields = await this.drawerFields;

      const errors = validateData(data, normalizedFields);

      if (errors !== true && errors.length > 0) {
        this.$panel.error(this.$t("libis.solis.error.fill.all.required.fields"));
        return;
      }
      this.isSaving = true;
      this.$panel.drawer.close();

      const formatedData = formatData(data, normalizedFields);

      try {
        const response = await fetch(this.createUrl + '?language=' + this.currentCode, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(formatedData)
        });

        const result = await response.json();
        console.log(result);

        if (result.status === 'success') {
          this.isSaving = false;
          if(result.excist) {
            this.$emit("select", result.result);
          }
          else {
            this.$emit("select", result.result);
          }
        }
        else {
          this.isSaving = false;
          this.$panel.error(result.message);
        }
      }
      catch (error) {
        this.$panel.error(error);
      }
    },
  },
};
</script>
<style>
.error-red {
  color: #f70303;
}

.succes-green {
  color: #0cbe33;
}

.k-form-drawer .k-input-element {
  text-align: left;
}

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