<template>
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" :value="modelValue" :id="id" :checked="checked || !!modelValue"
            :disabled="disabled" ref="inputSwitch" style="cursor: pointer;" @change="onChange" />
        <label class="form-check-label" :for="id">
            <slot></slot>
        </label>
    </div>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue'

export default defineComponent({
    props: {
        modelValue: {
            type: Boolean,
            default: false,
        },
        id: {
            type: String,
            required: true,
        },
        checked: {
            type: Boolean,
            default: false,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['update:modelValue'],
    setup() {
        const inputSwitch = ref<HTMLInputElement | null>(null);

        return {
            inputSwitch,
        }
    },
    methods: {
        onChange() {
            this.$emit('update:modelValue', this.inputSwitch?.checked);
        },
    }
})
</script>
