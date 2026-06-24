<template>
  <k-field>
    <header class="k-section-header">
      <h2 class="k-label k-section-label">{{ label }}:</h2>
    </header>

    <k-button
      variant="filled"
      icon="add"
      size="xs"
      @click="openDialog()"
    >
      {{ $t('libis.solis.add')[0].toUpperCase() + $t('libis.solis.add').slice(1) }}
    </k-button>

    <k-image-dialog v-if="openDialogValue" :disabled="disabled" :open="openDialogValue" :meta="objectMeta" :metaToCheck="meta" :pageId="pageId" :fileTemplate="fileTemplate"
    @close="handleClose" />
  </k-field>
</template>

<script>
export default {
  name: "imageUpload",
  props: {
    label: String,
    pageId: String,
    meta: {
      type: Object,
      default: () => ({})
    },
    fileTemplate: {
      type: String,
      Default: ""
    }
  },
  data() {
    return {
      openDialogValue: false,
      objectMeta: ({}),
    };
  },  
  created() {
    this.objectMeta = this.fieldsArrayToObject(this.meta);
  },
  methods: {
    openDialog() {
      this.openDialogValue = true;
    },    
    handleClose() {
      this.openDialogValue = false;
    },
    fieldsArrayToObject(fieldsArray) {
      return Object.fromEntries(
        fieldsArray.map(field => {
          const { name, ...rest } = field;
          return [name, rest];
        })
      );
    }

  }
};
</script>