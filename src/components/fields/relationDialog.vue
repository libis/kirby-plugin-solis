<template>
  <div v-if="open" class="k-dialog-overlay">
    <div class="k-dialog k-models-dialog">
      <div class="k-dialog-body">
        <div class="k-models-section-search k-input">
          <k-search-input :value="searchValue" @input="searchValue = $event" />
          <span class="k-input-icon" @click="search">
            <k-icon type="search" />
          </span>
        </div>

        <k-collection v-if="items" :items="items" :pagination="pagination" @paginate="onPaginate"
          :empty="emptyCollection" class="k-dialog-item-collection">
          <template #options="{ item: row }">
            <k-choice-input type="checkbox" :checked="isSelected(row.id)"
              :title="isSelected(row.id) ? $t('remove') : $t('select')" @input="toggle(row)"  />
          </template>
        </k-collection>
      </div>

      <div class="k-dialog-footer">
        <div class="k-button-group k-dialog-buttons">
          <k-button icon="cancel" variant="filled" @click="$emit('close')">{{ $t("libis.solis.cancel") }}</k-button>
          <k-button icon="check" variant="filled" theme="green" @click="confirm">{{ $t("libis.solis.confirm") }}</k-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "k-dialog-relation",
  props: {
    open: Boolean,
    values: Array,
    baseUrl: {
      type: String,
      default: '',
    },
    languageCode: String,
    textValue: String,
    infoValue: {
      type: String,
      default: '',
    },
    imageValue: {
      type: String,
      default: '',
    },
    linkValue: {
      type: String,
      default: ''
    },
    recordType: String,
  },
  data() {
    return {
      emptyCollection: { text: this.$t("libis.solis.no.results") },
      selected: this.values || [],
      searchValue: "",
      pagination: {
        page: 1,
        pages: 1,
        limit: 10,
        total: 0
      },
      items: [],
    };
  },
  created() {
    this.fetchItems(this.pagination.page);
  },
  mounted() {
    window.addEventListener('keydown', this.globalKeyListener);
  },
  beforeDestroy() {
    window.removeEventListener('keydown', this.globalKeyListener);
  },
  methods: {
    globalKeyListener(e) {
      if (e.key === 'Enter') {
        const active = document.activeElement;
        if (active && active.tagName === 'INPUT') {
          this.search();
        }
      }
    },
    isSelected(id) {
      return this.selected.some(item => item.id === id);
    },

    toggle(row) {
      const exists = this.selected.find(item => item.id === row.id);
      if (exists) {
        this.selected = this.selected.filter(item => item.id !== row.id);
      } else {
        this.selected.push({ id: row.id, text: row.text, info: row.info, image: row.image, link: row.linkUrl, target: row.target });
      }
    },
    confirm() {
      this.$emit("confirm", this.selected);
    },
    async fetchItems(page, q = "") {
      const query = q !== "" ? "q=" + q + "&" : "";
      let url = `/solis-records?${query}page=${page}&limit=${this.pagination.limit}&recordType=${this.recordType}`;
      if (this.baseUrl != '') {
        url += `&baseUrl=${encodeURIComponent(this.baseUrl)}`;
      }
      const response = await fetch(url);
      const data = await response.json();
      const rawItems = data.items;

      this.items = rawItems.map(item => {
        const paths = this.textValue.split('|').map(p => p.trim());
        const text = paths.map(path => this.getNestedValue(item.data[this.languageCode] || {}, path)).find(val => val !== undefined && val !== null && val !== '') || '';

        return {
          text: text,
          info: this.getNestedValue(item.data[this.languageCode] || {}, this.infoValue) || '',
          ...(this.getNestedValue(item.data[this.languageCode] || {}, this.imageValue)
            ? {
              image: {
                src: this.getNestedValue(item.data[this.languageCode] || {}, this.imageValue)
              }
            } : {}),
          id: item.id.split('/').filter(Boolean).pop(),
          ...(this.linkValue != ''
            ? {linkUrl: this.mapUrl(item.id.split('/').filter(Boolean).pop(), this.linkValue), target: '_blank'}
            : {}),
        };
      });

      this.pagination = data.pagination;
    },
    search() {
      this.fetchItems(1, this.searchValue);
    },
    onPaginate(page) {
      this.fetchItems(page.page, this.searchValue);
    },
    getNestedValue(obj, path) {
      return path
        .replace(/\[(\d+)\]/g, '.$1')
        .split('.')
        .reduce((acc, key) => acc && acc[key], obj);
    },
    mapUrl(source, url) {
      return url.replace(/{(.*?)}/g, (match, key) => source || '');
    },
  }
};
</script>

<style>
.k-dialog-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 9999;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
}

.buttons {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
}

.k-collection.k-dialog-item-collection .k-item .k-item-content {
  width: 90%;
}
</style>
