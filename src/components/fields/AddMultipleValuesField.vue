<template>
  <k-field>
    <div v-if="isSaving" class="processing-view">
      <div class="loader-component">
        Data wordt verwerkt...
      </div>
    </div>
    <header class="k-section-header ">
      <h2 class="k-label k-section-label">{{ label }}<span v-if="min >= 1" class="error-red">*</span></h2>
      <div class="k-button-group k-section-buttons">
        <k-button variant="filled" icon="add" @click="openDrawer()" size="xs" :disabled="disabled" v-if="canAdd">
          {{ $t('libis.solis.add').charAt(0).toUpperCase() + $t('libis.solis.add').slice(1) }}
        </k-button>
      </div>
    </header>
    <div class="k-layouts k-draggable multiple-values-wrapper">
      <div v-for="(record, index) in data" :key="record.id">
        <div class="k-block-container add-multiple-value-field-fields-container" :style="styleClasses">
          <div class="k-block-type-fields-header">
            <div class="k-block-title">{{ singularTypeName }}</div>
            <div class="k-button-group multiple-values-buttons">
              <k-button icon="edit" @click="openDrawer(record)" size="xs" :disabled="disabled" />
              <k-button icon="trash" @click="openDeleteRecord(record)" size="xs" :disabled="!canDelete" />
            </div>
          </div>
          <k-fieldset :disabled="true" :fields="fields" :value="record" />
        </div>
      </div>
    </div>
  </k-field>
</template>

<script>
import { formatData, validateData } from '../../functions/processData';
export default {
  name: "multiple-values-field",
  props: {
    fields: Object,
    value: {
      type: Array,
      default: () => []
    },
    label: String,
    singularTypeName: String,
    multiple: {
      type: Boolean,
      default: true
    },
    styleClasses: {
      type: Object,
      default: () => ({})
    },
    min: {
      type: Number,
      default: 0
    },
    disabled: {
      type: Boolean,
      default: false
    },
    url: {
      type: String
    },
    delete: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      data: [...this.value],
      currentCode: window.panel.language.code,
      isSaving: false
    };
  },
  computed: {
    canAdd() {
      return this.multiple || this.data.length === 0
    },
    canDelete() {
      return this.disabled == false && this.delete == true
    },
    drawerFields() {
      if (!this.fields) return {};
      return Object.fromEntries(
        Object.entries(this.fields).map(([key, field]) => [
          key,
          field.type === "add_multiple_values_field"
            ? { ...field, name: field.name ?? key, onChangeRecord: () => { } }
            : { ...field, name: field.name ?? key }
        ])
      );
    }
  },
  watch: {
    value: {
      handler(newVal) {
        this.data = Array.isArray(newVal) ? [...newVal] : { ...newVal };
      },
      deep: true
    }
  },
  methods: {
    emitChange() {
      if(this.disabled == true) return;
      const output = Array.isArray(this.value) ? [...this.data] : { ...this.data };
      this.$emit("change:record", output);
      this.$emit("input", output);
    },
    emptyValue() {
      if (!this.fields) return {};
      return Object.fromEntries(
        Object.keys(this.fields).map(key => {
          const field = this.fields[key];
          if (field.type === "add_multiple_values_field") {
            return [key, []];
          }
          return [key, ""];
        })
      );
    },
    openDrawer(record = null) {
      if(this.disabled == true) return;
      const isEdit = !!record;
      const value = isEdit ? JSON.parse(JSON.stringify(record || this.data)) : this.emptyValue()

      this.$panel.drawer.open({
        component: "k-form-drawer",
        props: {
          icon: "box",
          title: "Field",
          fields: this.drawerFields,
          value,
          options: isEdit
            ? [{ icon: "trash", text: this.$t('libis.solis.delete'), click: () => this.openDeleteRecord(record) }]
            : [{ icon: "cancel", text: this.$t('libis.solis.cancel'), click: () => this.$panel.drawer.close() }]
        },
        on: {
          submit: data => this.updateData(data, isEdit),
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

    async updateData(data, isEdit) {
      if(this.disabled == true) return;
      const errors = validateData(data, this.drawerFields);

      if (errors !== true && errors.length > 0) {
        this.$panel.error(this.$t("libis.solis.error.fill.all.required.fields"));
        return;
      }
      this.isSaving = true;
      this.$panel.drawer.close();

      if (isEdit) {
        const formatedData = formatData(data, this.drawerFields);
        try {
          const requestUrl = this.url + '/' + data.id + '/' + this.currentCode;
          const response = await fetch(requestUrl, {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(formatedData)
          });

          const result = await response.json();

          if (result.status === 'success') {
            this.isSaving = false;
            const index = this.data.findIndex(item => item.id === data.id);
            if (index !== -1) this.data.splice(index, 1, { ...this.data[index], ...data });
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
      else {
        const formatedData = formatData(data, this.drawerFields);

        try {
          const response = await fetch(this.url, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(formatedData)
          });

          const result = await response.json();

          if (result.status === 'success') {
            this.isSaving = false;
            data.id = result.result.data[0].id;
            this.data.push(data);
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

      this.emitChange();
    },
    openDeleteRecord(record) {
      if(this.disabled == true) return;
      if(this.canDelete == false) return;
      this.$panel.dialog.open({
        component: 'k-remove-dialog',
        props: {
          text: this.$t("libis.solis.delete.message.simple"),
        },
        on: {
          cancel: () => this.$panel.dialog.close(),
          close: () => this.$panel.dialog.close(),
          submit: data => this.deleteRecord(record),
        }
      })
    },
    deleteRecord(record) {
      if(this.disabled == true) return;
      if(this.canDelete == false) return;
      this.$panel.dialog.close();
      this.isSaving = true;
      this.data = this.data.filter(item => item.id !== record.id);
      this.emitChange();
      this.isSaving = false;
      this.$panel.drawer.close();
    }
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

.add-multiple-value-field-fields-container {
  padding-bottom: 20px;
  padding-top: 5px;
}

.add-multiple-value-field-fields-container .k-block-type-fields-header {
  margin-bottom: 15px;
  border-bottom: 1px solid #f0f0f0;
}

.add-multiple-value-field-fields-container .k-fieldset .k-grid {
  gap: 20px;
}

.multiple-values-wrapper {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.multiple-values-buttons {
  gap: 5px;
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