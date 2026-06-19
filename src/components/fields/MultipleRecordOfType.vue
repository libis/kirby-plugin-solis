<template>
    <k-field>
        <header class="k-section-header">
            <h2 class="k-label k-section-label">{{ label }}<span v-if="min >= 1" class="error-red">*</span></h2>
            <div class="k-button-group k-section-buttons">
                <k-button variant="filled" icon="add" @click="addInput()" size="xs" :disabled="disabled">
                    {{ $t('libis.solis.add').charAt(0).toUpperCase() + $t('libis.solis.add').slice(1) }}
                </k-button>
            </div>
        </header>
        <div class="item-wrapper">
            <div class="k-input-wrapper" v-for="(input, index) in inputs" :disabled="disabled" :key="options?.length || index">
                <k-input v-if="valueSelector !== null" :type="inputType" v-model="inputs[index][valueSelector]"
                    :disabled="disabled" @input="onInputChange(index, $event)" :options="options" />
                <k-input v-else :type="inputType" v-model="inputs[index]" :disabled="disabled"
                    @input="onInputChange(index, $event)" :options="options" />
                <k-icon v-if="inputType == 'select'" type="angle-down" />
                <k-button title="delete" icon="remove" :disabled="disabled" @click="remove(index)" />
            </div>
        </div>
    </k-field>
</template>

<script>
export default {
    name: "multiple-records-of-type",
    props: {
        value: {
            type: Array,
            default: () => []
        },
        label: String,
        inputType: String,
        valueSelector: {
            type: String,
            default: null
        },
        disabled: {
            type: Boolean,
            default: false
        },
        options: {
            type: Array,
            default: []
        },
        min: Number,
        disabled: {
            type: Boolean,
            default: false
        },
    },
    data() {
        return {
            inputs: Array.isArray(this.value) ? this.value.slice() : [],
        };
    },
    methods: {
        emitChange() {
            if(this.disabled == true) return;
            this.$emit("input", this.inputs);
            this.$emit("change:record", this.inputs);
        },

        addInput() {
            if(this.disabled == true) return;
            if (this.valueSelector == null) {
                this.inputs = this.inputs.filter(input =>
                    typeof input === "string" && input.trim() !== ""
                );
                this.inputs.push("");
            }
            else {
                this.inputs = this.inputs.filter(input => {
                    const value = input?.[this.valueSelector];
                    return typeof value === "string" && value.trim() !== "";
                });

                this.inputs.push({ [this.valueSelector]: "" });
            }

            this.emitChange();
        },
        onInputChange(index, value) {
            if(this.disabled == true) return;
            if (this.valueSelector == null) {
                this.inputs[index] = value;
                this.inputs = this.inputs.filter((input, i) => input !== "" || i === index);
            }
            else {
                this.inputs[index][this.valueSelector] = value;
                this.inputs = this.inputs.filter((input, i) => input[this.valueSelector] !== "" || i === index);
            }
            this.emitChange();
        },
        remove(index) {
            if(this.disabled == true) return;
            this.inputs = this.inputs.filter((_, i) => i !== index);
            this.emitChange();
        }
    },
    watch: {
        value: {
            handler(newVal) {
                if (!Array.isArray(newVal)) {
                    this.inputs = [];
                    return;
                }
                this.inputs = newVal.map((item) =>
                    typeof item === "object" && item !== null ? { ...item } : item
                );
            },
            deep: true
        }
    }

};
</script>
<style>
.error-red {
    color: #f70303;
}

.succes-green {
    color: #0cbe33;
}

.item-wrapper {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.k-input-wrapper {
    display: flex;
    flex-direction: row;
    gap: 10;
    position: relative;
}

.k-input-wrapper .k-input {
    width: 100%;
}

.k-input-wrapper .k-icon[data-type="angle-down"] {
    margin-top: 8px;
    z-index: 99;
    width: 20px;
    position: absolute;
    right: 40px;
}
</style>