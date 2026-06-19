<template>
  <k-panel-inside>
    <k-view>
      <k-header>
        Records
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
        <header class="k-section-header page-header">
          <h2 class="k-label k-section-label">
            Records
          </h2>
          <div class="k-button-group k-section-buttons">
            <k-button 
              variant="filled" 
              icon="search" 
              @click="openSearch" 
              size="xs"
            >
              Search
            </k-button>
          </div>
        </header> 
        <div class="k-models-section-search k-input" v-if="searchOpen">
          <k-search-input 
            :value="searchValue" 
            @input="searchValue = $event" 
          />
          <span class="k-input-icon" @click="search">
            <k-icon type="search" />
          </span>
        </div>
        <k-collection
          class="records-collection"
          v-if="items"
          :items="items"
          layout="cards"
          :pagination="pagination"
          @paginate="onPaginate"
          :empty="emptyCollection"
        />
      </div>
	  </k-view>
  </k-panel-inside>
</template>

<script>
export default {
	props: {
        recordsTypes: Array,
    },
    data() {
    return {
      items: this.recordsTypes,
      pagination: {
        page: 1,
        pages: 1,
        limit: 20,
        total: 0
      },
      searchOpen: false,
      searchValue: '',
      emptyCollection: { text: "Geen records"},
      currentCode: window.panel.language.code,
    };
  },
  created() {
    this.updatePagination();
    this.fetchItems(this.pagination.page);
  },  
  mounted() {
    window.addEventListener('keydown', this.globalKeyListener);
  },
  beforeDestroy() {
    window.removeEventListener('keydown', this.globalKeyListener);
  },
  methods: {    
    updatePagination() {
      const filtered = this.filteredRecords();
      this.pagination.total = filtered.length;
      this.pagination.pages = Math.ceil(filtered.length / this.pagination.limit);
    },
    filteredRecords() {
      if (!this.searchValue) return this.recordsTypes;
      return this.recordsTypes.filter(item =>
        JSON.stringify(item).toLowerCase().includes(this.searchValue.toLowerCase())
      );
    },
    fetchItems(page) {
      this.pagination.page = page;
      const filtered = this.filteredRecords();
      this.updatePagination();

      const start = (page - 1) * this.pagination.limit;
      const end = start + this.pagination.limit;
      this.items = filtered.slice(start, end);
    },
    globalKeyListener(e) {
      if (e.key === 'Enter' && this.searchOpen) {
        const active = document.activeElement;
        if (active && active.tagName === 'INPUT') {
          this.search();
        }
      }
    },
    search() {
      this.fetchItems(1);
    },
    onPaginate(page) {
      this.fetchItems(page.page);
    },
    openSearch() {
      if(this.searchOpen) {
        this.searchOpen = false;
        this.searchValue = "";
        this.search();
      }
      else {
        this.searchOpen = true;
      }
    },
  },
};
</script>

<style>
  .page-header {
    margin-bottom: 15px;
  }
  .records-collection .k-icon-frame svg {
    color: #bdbdbd;
  }
</style>