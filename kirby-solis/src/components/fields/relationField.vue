<template>
  <k-field>
    <header class="k-section-header">
      <h2 class="k-label k-section-label">{{ label }}<span v-if="min >= 1" class="error-red">*</span></h2>
      <span :class="['text-sm ml-2', isInvalid ? 'error-red' : 'succes-green']">
        {{ selectedCount }}
        <span v-if="max"> / {{ isObjectSelected ? 1 : max }}</span>
      </span>

      <div class="k-button-group k-section-buttons">
        <k-button variant="filled" icon="search" :disabled="disabled" @click="openDialog" size="xs">
          {{ $t("libis.solis.search") }}
        </k-button>
      </div>
    </header>

    <k-collection v-if="normalizedSelected.length" :disabled="disabled" :items="normalizedSelected" :sortable="sortable" @sort="handleSort">
      <template #options="{ index }">
        <k-button :title="$t('libis.solis.delete')" icon="remove" :disabled="disabled" @click="remove(index)" />
      </template>
    </k-collection>
    
    <k-dialog-relation v-if="openDialogValue" :disabled="disabled" :open="openDialogValue" :values="tempSelected" :baseUrl="baseUrl"
      :languageCode="currentCode" :textValue="textValue" :infoValue="infoValue" :imageValue="imageValue" :linkValue="linkValue"
      :recordType="recordType" @close="handleClose" @confirm="handleConfirm" />
  </k-field>
</template>

<script>
import { parse } from 'yaml';
export default {
  name: "relationField",
  props: {
    value: {
      type: [Array, Object],
      default: () => [],
    },
    selected: {
      type: [Array, Object],
      default: () => [],
    },
    baseUrl: {
      type: String,
      default: '',
    },
    textValue: String,
    infoValue: {
      type: String,
      default: '',
    },
    imageValue: {
      type: String,
      default: ''
    },
    linkValue: {
      type: String,
      default: ''
    },
    recordType: String,
    label: String,
    min: Number,
    max: Number,
    kirbyPage: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    },
    sortable: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      openDialogValue: false,
      currentCode: window.panel.language.code,
      tempSelected: [],
    };
  },
  computed: {
    selectedProxy: {
      get() {
        if (this.kirbyPage) {
          return this.parseKirbyYamlToArray(this.value);
        }
        const selected = this.selected;
        if (Array.isArray(selected)) return selected;
        if (selected && typeof selected === 'object') return selected;
        return [];
      },
      set(next) {
        const safe = JSON.parse(JSON.stringify(next ?? []));
        if (this.kirbyPage) {
          this.$emit('input', safe);
        } 
        else {
          this.$emit('update:selected', safe);
        }
      },
    },
    isInvalid() {
      const count = this.selectedCount;
      const minOk = this.min ? count >= this.min : true;
      const maxOk = this.max ? count <= this.max : true;
      return !(minOk && maxOk);
    },
    isObjectSelected() {
      return !Array.isArray(this.selectedProxy);
    },
    normalizedSelected() {
      const items = this.selectedProxy;
      if (Array.isArray(items)) return items;
      if (items && typeof items === 'object') return [items];
      return [];
    },
    selectedCount() {
      const items = this.selectedProxy;
      return Array.isArray(items) ? items.length : (items ? 1 : 0);
    },
  },
  methods: {
    openDialog() {
      if(this.disabled == true) return;
      this.tempSelected = [...this.normalizedSelected];
      this.openDialogValue = true;
    },
    handleConfirm(data) {
      if(this.disabled == true) return;
      this.openDialogValue = false;

      let next = data ?? [];
      if (this.max && Array.isArray(next) && next.length > this.max) {
        next = next.slice(-this.max);
      }

      if (this.isObjectSelected && !this.kirbyPage) {
        const single = Array.isArray(next) ? next[0] || null : (next || null);
        this.selectedProxy = single;
      }
      else {
        this.selectedProxy = next;
      }

      this.tempSelected = [];
    },
    handleClose() {
      if(this.disabled == true) return;
      this.tempSelected = [];
      this.openDialogValue = false;
    },
    handleSort(event) {
      this.selectedProxy = event;
    },
    remove(index) {
      if(this.disabled == true) return;
      if (this.isObjectSelected && !this.kirbyPage) {
        this.selectedProxy = null;
      } 
      else {
        const updated = [...this.normalizedSelected];
        updated.splice(index, 1);
        this.selectedProxy = updated;
      }
    },
    parseKirbyYamlToArray(yamlStr) {
      if (typeof yamlStr !== 'string') return yamlStr;

      return parse(yamlStr);
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
</style>