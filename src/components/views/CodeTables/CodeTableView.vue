<template>
  <k-panel-inside>
    <k-view>
      <k-header>
        {{ $t("libis.solis.codeTables") }}
        <k-button-group slot="buttons">
          <k-view-button
            icon="question"
            text="Helpdocs"
            link="https://heron.helpdocsite.com/erfgoedparels"
            target="_blank"
            theme="blue"
          />
          <language-dropdown-button />
        </k-button-group>
      </k-header>
      <div class="page-view">
        <div data-variant="fields" class="k-grid">
          <div v-if="codeTables" v-for="(value, index) in codeTables" class="codetable-section" :style="value.style"
            :name="value.name">
            <header class="k-section-header">
              <h2 class="k-label k-section-label">{{ value.label }}</h2>
              <k-button icon="add" @click="openPopUp(value.name)">{{ $t("libis.solis.add") }}</k-button>
            </header>
            <k-collection name="entries" :items="value.values" class="codetable-collection">
              <template #options="{ index }">
                <k-button title="edit" v-if="canEdit" icon="edit" @click="openPopUp(value.name, value.values[index])" />
                <k-button title="delete" v-if="canDelete" icon="trash" @click="openDelete(value.values[index], value.name)" />
              </template>
            </k-collection>
            <div class="codetable-footer-section">
              <k-button variant="filled" v-if="canEdit" icon="add" size="xs" @click="openPopUp(name)" />
            </div>
          </div>
        </div>
      </div>
    </k-view>
  </k-panel-inside>
</template>

<script>
import { sanitizeText } from '../../../functions/processData';
export default {
  name: "CodeTablesView",
  props: {
    mainInfo: Array,
    codeTables: Array,
    role: {
      type: String,
      default: 'reader',
    }
  },
  data() {
    return {
      currentCode: window.panel.language.code,
    };
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
  },
  methods: {
    openPopUp(name, editRecord = null) {
      if(this.canEdit == false) return;
      const isEdit = !!editRecord;
      const value = {
        record: isEdit ? editRecord.text : ""
      };
      const submitText = isEdit ? this.$t("libis.solis.edit") : this.$t("libis.solis.add.btn");

      this.$panel.dialog.open({
        component: "k-form-dialog",
        props: {
          submitButton: {
            text: submitText,
          },
          fields: {
            record: {
              label: this.$t("libis.solis.codeTables.title"),
              type: 'text',
              required: true,
            },
          },
          value,
        },
        on: {
          cancel: () => this.$panel.dialog.close(),
          close: () => this.$panel.dialog.close(),
          submit: (data) => {
            if (isEdit && data.record != null) {
              this.editRecord(editRecord, data.record, name);
            }
            else if (data.record != null) {
              this.addRecord(data, name);
            }
            else {
              this.$panel.error(this.$t('libis.solis.error.fill.all.fields'));
            }
          }
        }
      });
    },
    async editRecord(oldRecord, newRecord, name) {
      if(this.canEdit == false) return;
      const id = oldRecord.value.split('/').filter(Boolean).pop();
      const value = sanitizeText(newRecord);
      const record = { id: id, value: value };
      const link = this.mainInfo.requestsInKirbyLink + '/' + name + '/' + id + '/' + this.currentCode;

      try {
        const response = await fetch(link, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(record)
        });

        const result = await response.json();

        if (result.status === 'success') {
          this.$panel.dialog.close();
          window.location.reload();
        }
        else {
          this.$panel.error(result.message);
        }
      }
      catch (error) {
        this.$panel.error(error);
      }
    },
    async addRecord(record, name) {
      if(this.canEdit == false) return;
      const id = sanitizeText(record.record).replace(/\s+/g, '-');
      const value = sanitizeText(record.record);
      const newRecord = { id: id, value: value };
      const link = this.mainInfo.requestsInKirbyLink + '/' + name + '/' + this.currentCode;

      try {
        const response = await fetch(link, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(newRecord)
        });

        const result = await response.json();

        if (result.status === 'success') {
          this.$panel.dialog.close();
          window.location.reload();
        }
        else {
          this.$panel.error(result.message);
        }
      }
      catch (error) {
        this.$panel.error(error);
      }
    },
    openDelete(item, type) {
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
              text: '<h3>' + this.$t("libis.solis.delete.message.title", {title: item.text}) + '</h3>' + 
                    '<p style="margin-top:5px;">' + this.$t("libis.solis.delete.message.text") + '</p>',
              theme: false,
            }
          },
        },
        on: {
          cancel: () => this.$panel.dialog.close(),
          close: () => this.$panel.dialog.close(),
          submit: (data) => {
            this.deleteRecord(item, type);
          }
        }
      })
    },
    async deleteRecord(item, type) {
      if(this.canDelete == false) return;
      try {
        const response = await fetch(`${this.mainInfo.requestsInKirbyLink}/${type}/${item.value.split('/').filter(Boolean).pop()}`, { method: 'DELETE' });

        const result = await response.json();
        if (result.status === 'success') {
          window.location.reload();
        }
        else {
          this.$panel.error(result.message);
        }
      }
      catch (error) {
        this.$panel.error(error);
      }
    }
  },
};
</script>

<style>
.codetable-footer-section {
  display: flex;
  justify-content: center;
  margin-top: 10px;
}

.codetable-collection .k-item-options {
  margin-right: 10px;
  display: flex;
  gap: 5px;
}

.codetable-collection .k-item-options .k-button {
  width: 22px;
  height: 22px;
}
</style>