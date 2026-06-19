<template>
  <div v-if="open" class="k-dialog-overlay">
    <div class="k-dialog k-models-dialog k-image-dialog">
      <div class="k-dialog-body">
        <div @click="fileOpenSelect" v-if="pickedFiles.length == 0">
          <k-dropzone @drop="onFilesDropped">
            <k-box
              theme="empty"
              text="Drag files here …"
              height="5rem"
              icon="upload"
              align="center"
            >
              {{ $t("files.empty") }}
            </k-box>
          </k-dropzone>
          <input
            ref="fileInput"
            type="file"
            style="display: none"
            :accept="acceptFiles"
            multiple
            required
            @change="onFilesPicked"
          />
        </div>
        <div v-else class="wizard">
          <div class="wizard-header">
            <h3 class="wizard-counter">
              Afbeelding {{ currentIndex + 1 }} van {{ pickedFiles.length }}
            </h3>
          </div>
          <div class="wizard-content">
            <div class="preview">
              <k-upload-item
                :extension="currentItem.extension"
                :type="currentItem.type"
                :name="currentItem.name"
                :niceSize="currentItem.size"
                :url="currentItem.url"
                :progress="currentItem.progress"
                :error="currentItem.error"
                @remove="onRemove"
                @rename="onRename"
              />
            </div>
            <div class="meta"> 
              <k-fieldset
                :fields="meta"
                :value="currentItem.meta"
                @input="value => currentItem.meta = value"
              />
            </div>
          </div>
          <div class="wizard-nav">
            <k-button icon="angle-left" :disabled="currentIndex === 0" @click="prev"></k-button>
            <k-button icon="angle-right" :disabled="currentIndex === pickedFiles.length - 1" @click="next"></k-button>
          </div>
        </div>
      </div>
      <div class="k-dialog-footer">
        <div class="k-button-group k-dialog-buttons">
          <k-button icon="cancel" variant="filled" @click="$emit('close')">{{ $t("libis.solis.cancel") }}</k-button>
          <k-button icon="upload" variant="filled" theme="green" :disabled="pickedFiles.length === 0 || isUploading" @click="onSubmitFiles">{{ $t("upload") }}</k-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { validateData} from '../../functions/processData';

export default {
  name: "k-image-dialog",
  props: {
    open: Boolean,
    meta: {
      type: Object,
      default: () => ({})
    },
    metaToCheck: {
      type: Array,
      default: []
    },
    fileTemplate: {
      type: String,
      Default: ""
    },
    pageId: {
      type: String,
      default: ""
    },
  },  
  data() {
    return {
      pickedFiles: [],
      isUploading: false,
      currentIndex: 0,
    }
  },
  computed: {
    currentItem() {
      return this.pickedFiles[this.currentIndex] || null;
    },
    acceptFiles() {
      if(!this.fileTemplate || !this.fileTemplate.accept) return "";

      if(this.fileTemplate.accept.mime) {
        return Array.isArray(this.fileTemplate.accept.mime) ? this.fileTemplate.accept.mime.join(",") : this.fileTemplate.accept.mime;
      }
      else if(this.fileTemplate.accept.extension) {
        if (typeof this.fileTemplate.accept.extension === "string") {
          this.fileTemplate.accept.extension = this.fileTemplate.accept.extension.split(",").map(e => e.trim()).filter(Boolean);
        }
        if (!Array.isArray(this.fileTemplate.accept.extension)) {
          this.fileTemplate.accept.extension = [this.fileTemplate.accept.extension];
        }
        return this.fileTemplate.accept.extension.map(e => "." + e).join(",");
      }
      else if(this.fileTemplate.accept.type) {
        return `${this.fileTemplate.accept.type}/*`;
      }
      else {
        return "";
      }
    }
  },
  methods: { 
    fileOpenSelect() {
      this.$refs.fileInput?.click();
    },  
    onFilesDropped(files) {
      files = Array.from(files);
      this.pickedFiles = this.fileFormatter(files);
      this.currentIndex = 0;
    },
    onFilesPicked(e) {
      const files = Array.from(e.target.files || []);
      this.pickedFiles = this.fileFormatter(files);
      this.currentIndex = 0;
      e.target.value = "";
    },
    fileFormatter(files) {
      const now = Date.now();
      return files.map((file, i) => {
        const originalName = file.name || "untitled";
        const name = originalName.includes(".")
          ? originalName.split(".").slice(0, -1).join(".")
          : originalName;
        const extension = (originalName.includes(".") ? originalName.split(".").pop() : "")?.toLowerCase() || "";
        return {
          id: name,
          name,
          originalName: name,
          extension,
          type: file.type || "",
          size: file.size || 0, 
          file,
          url: URL.createObjectURL(file),
          progress: 0,
          error: null,
          meta: this.EmptyMetaField(),
        };
      });
    },
    EmptyMetaField() {
      const fields = this.meta || {};
      const output = {};
      for(const [id, element] of Object.entries(fields)) {
        if(Object.hasOwn(element, "default")) {
          output[id] = this.cloneDefault(element.default);
          continue;
        }
        const fieldType = (element.type || "").toLowerCase();
        if (["tags", "multiselect", "checkboxes", "structure"].includes(fieldType)) {
          output[id] = [];
        }
        else if (["number", "range"].includes(fieldType)) {
          output[id] = 0;
        }
        else if (["toggle", "switch", "checkbox"].includes(fieldType)) {
          output[id] = false;
        }
        else {
          output[id] = "";
        }
      }
      return output;
    },
    prev() {
      if (this.currentIndex > 0) this.currentIndex--;

    },
    next() {
      if (this.currentIndex < this.pickedFiles.length - 1) this.currentIndex++;
    },
    onRemove(file) {
      if (!this.currentItem) return;
      const item = this.pickedFiles.splice(this.currentIndex, 1)[0];
      if (item?.url) URL.revokeObjectURL(item.url);
      if (this.currentIndex >= this.pickedFiles.length)
        this.currentIndex = Math.max(0, this.pickedFiles.length - 1);
    },
    onRename(name) {
      this.currentItem.name = name;
    },
    async onSubmitFiles() {
      try {
        this.isUploading = true;

        for (const item of [...this.pickedFiles]) {
          item.progress = 10;
          const validationResult = validateData(item.meta, this.metaToCheck, this.$t);

          if (validationResult !== true) {
            item.error = validationResult[0].message;
          }
          else {
            item.progress = 40;
            const form = new FormData();
            form.append("file", item.file);
            form.append("name", item.name);
            form.append("extension", item.extension);
            form.append("meta", JSON.stringify(item.meta));
            if (this.fileTemplate && this.fileTemplate.name) {
              form.append("template", this.fileTemplate.name);
            }
            else {
              form.append("template", "default");
            }
            form.append("pageId", this.pageId);

            
            const res = await fetch("/solis-custom-image-upload", {
              method: "POST",
              body: form
            });
              
            const data = await res.json();

            if (data.status === "error") {
              item.error = data.message;
            }
            else if(data.status === "success") {
              item.progress = 100;
              this.pickedFiles = this.pickedFiles.filter(f => f !== item);
              if (this.pickedFiles.length === 0) {
                this.open = false;
                window.location.reload();
              }
            }
          }
        }
        this.isUploading = false;
      }
      catch(error) {
        this.$panel.error(error);
      }
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

.k-dialog.k-image-dialog {
  width: 50%;
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

.wizard {
  display: flex; 
  flex-direction: column;
  gap: 10px; 
}

.wizard-content {
  display: flex; 
  flex-direction: column;
  gap: 20px; 
}

.wizard-counter {
  font-size: 1.5em;
  font-weight: 600;
  margin-bottom: 10px;
}
</style>
