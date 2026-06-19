<template>
  <div class="k-view-button k-languages-dropdown">
    <k-button icon="translate" :dropdown="true" variant="filled" :text="currentCode.toUpperCase()"
      @click="$refs.dropdown.toggle()" />
    <k-dropdown-content ref="dropdown" :options="languageOptions" align-x="x" align-y="y">
      <template #item="{ item: language, index }">
        <k-button :key="'item-' + index" v-bind="language" class="k-dropdown-item k-languages-dropdown-item"
          @click="change(language)">
          {{ language.text }}

          <span class="k-languages-dropdown-item-info">
            <span class="k-languages-dropdown-item-code">
              {{ language.code ? language.code.toUpperCase() : '' }}
            </span>
          </span>
        </k-button>
        <hr v-if="language.isDefault" class="k-languages-divider" />
      </template>
    </k-dropdown-content>
  </div>
</template>

<script>
export default {
  data() {
    return {
      languages: window.panel.languages,
      currentCode: window.panel.language.code,
    };
  },

  computed: {
    currentLanguage() {
      return this.languages.find(lang => lang.code === this.currentCode);
    },
    languageOptions() {
      const defaultLang = this.languages.find(lang => lang.default);
      const otherLangs = this.languages.filter(lang => !lang.default);

      const format = (lang, isDefault = false) => ({
        text: lang.name,
        code: lang.code,
        changes: lang.changes ?? false,
        current: lang.code === this.currentCode,
        isDefault
      });

      return [
        format(defaultLang, true),
        ...otherLangs.map(lang => format(lang))
      ];
    }

  },

  methods: {
    change(language) {
      this.currentCode = language.code
      const path = window.location.pathname;
      const params = new URLSearchParams({ language: language.code });
      window.location.href = `${path}?${params.toString()}`;
    }
  }
};
</script>

<style></style>
