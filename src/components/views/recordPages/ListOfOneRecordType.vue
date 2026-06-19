<template>
  <k-panel-inside>
    <k-view>
      <k-header>
        {{ capitalizedFirstLetter(mainInfo.type) }}
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
      <k-tabs tab="items" :tabs="tabs" />
      <div class="page-view">
        <div v-if="isSaving" class="processing-view">
          <div class="loader-component">
            Data wordt verwerkt...
          </div>
        </div>
        <header class="k-section-header page-header">
          <h2 class="k-label k-section-label">
            {{ capitalizedFirstLetter(mainInfo.type) }}
          </h2>
        </header>
        <div class="k-models-section-search k-input">
          <k-search-input :value="searchValue" @input="searchValue = $event" />
          <span class="k-input-icon" @click="search">
            <k-icon type="search" />
          </span>
        </div>
        <k-collection v-if="records" :items="records" :pagination="pagination" @paginate="onPaginate"
          :empty="emptyCollection" />
      </div>
    </k-view>
  </k-panel-inside>
</template>

<script>
export default {
  name: "Record List",
  props: {
    mainInfo: Array,
    tabs: Array,
    titleSelector: String,
    infoSelector: {
      type: String,
      default: '',
    },
    imageSelector: {
      type: String,
      default: '',
    },
    role: {
      type: String,
      default: 'reader',
    }
  },
  data() {
    return {
      records: [],
      pagination: {
        page: 1,
        pages: 1,
        limit: 20,
        total: 0
      },
      searchValue: '',
      emptyCollection: { text: this.$t("libis.solis.no.results") },
      currentCode: window.panel.language.code,
      baseUrl: '',
    };
  },
  mounted() {
    this.currentCode = window.panel.language.code;
    this.fetchItems(this.pagination.page);
    window.addEventListener('keydown', this.globalKeyListener);
  },
  beforeDestroy() {
    window.removeEventListener('keydown', this.globalKeyListener);
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
    getNestedValue(obj, path) {
      return path.split('.').reduce((item, path) => item?.[path], obj);
    },
    capitalizedFirstLetter(word) {
      return word.charAt(0).toUpperCase() + word.slice(1)
    },
    getLastSegment(url) {
      return url.split('/').filter(Boolean).pop();
    },
    globalKeyListener(e) {
      if (e.key === 'Enter') {
        const active = document.activeElement;
        if (active && active.tagName === 'INPUT') {
          this.search();
        }
      }
    },
    async fetchItems(page, q = "") {
      const query = q !== "" ? "q=" + q + "&" : "";
      let url = `/solis-records?${query}page=${page}&limit=${this.pagination.limit}&recordType=${this.mainInfo.searchType}`;

      const response = await fetch(url);
      const data = await response.json();
      const rawItems = data.items;

      this.records = rawItems.map(item => {
        const data = item.data[this.currentCode];
        const infoValue = this.infoSelector != '' || this.infoSelector != undefined
          ? this.getNestedValue(data, this.infoSelector)
          : undefined;

        const imageValue = this.imageSelector != '' || this.imageSelector != undefined
          ? this.getNestedValue(data, this.imageSelector)
          : undefined;

        return {
          text: this.getNestedValue(item.data[this.currentCode], this.titleSelector),
          ...(infoValue != null && infoValue !== '' ? { info: infoValue } : {}),
          ...(imageValue != null && imageValue!== '' ? { image: { src:imageValue } } : {}),
          id: this.getLastSegment(item.id),
          link: this.mainInfo.requestsInKirbyLink + '/' + encodeURIComponent(this.getLastSegment(item.id)) + '?language=' + this.currentCode,
          options: [
            {
              text: this.$t("libis.solis.edit"),
              icon: 'edit',
              click: () => {
                if(this.canEdit == false) return;
                this.$panel.redirect(this.mainInfo.requestsInKirbyLink + '/' + encodeURIComponent(this.getLastSegment(item.id)) + '?language=' + this.currentCode);
              }
            },
            {
              text: this.$t("libis.solis.delete"),
              icon: 'trash',
              click: () => {
                this.openDelete(item);
              }
            },
          ],
        }
      });

      this.pagination = data.pagination;
    },
    search() {
      this.fetchItems(1, this.searchValue);
    },
    onPaginate(page) {
      this.fetchItems(page.page, this.searchValue);
    },
    openDelete(item) {
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
              text: '<h3>' + this.$t("libis.solis.delete.message.title", {title: this.getNestedValue(item.data[this.currentCode], this.titleSelector)}) + '</h3>' + 
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
            if (data.check.trim().toLowerCase() === this.getNestedValue(item.data[this.currentCode], this.titleSelector).trim().toLowerCase()) {
              this.deleteRecord(item);
            }
            else {

            }
          }
        }
      })
    },
    async deleteRecord(record) {
      this.$panel.dialog.close();
      this.isSaving = true;
      if(this.canDelete == false) return;
      try {
        const response = await fetch(`${this.mainInfo.requestsInKirbyLink}/${this.getLastSegment(record.id)}`, { method: 'DELETE' });

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
  },
};
</script>

<style>
.page-header {
  margin-bottom: 15px;
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
