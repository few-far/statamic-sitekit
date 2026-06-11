document.addEventListener('alpine:init', function () {
    Statamic.$components.register('html_select-fieldtype', {
        template: `
            <ui-combobox
                :id="id"
                :clearable="config.clearable"
                :disabled="config.disabled"
                :label-html="config.label_html"
                :max-selections="config.max_items"
                :model-value="value"
                :multiple="config.multiple"
                :options="options"
                :placeholder="__(config.placeholder)"
                :read-only="isReadOnly"
                :searchable="config.searchable || config.taggable"
                :taggable="config.taggable"
                :close-on-select="(config.taggable && !options.length) || !config.multiple"
                @update:modelValue="(value) => {
                    update(value)
                }"
            >
                <template #selected-option="{ option }">
                    <div class="flex items-center faf:justify-between w-full">
                        <span v-text="option.label" />
                        <span v-html="option.icon" />
                    </div>
                </template>

                <template #option="option">
                    <div
                        class="flex items-center faf:justify-between w-full"
                    >
                        <span v-text="option.label" />
                        <span v-html="option.icon" />
                    </div>
                </template>
            </ui-combobox>
        `,
        name: "HTMLSelectFieldtype",

        props: {
            value: [String, Array],
            config: Object,
            name: String,
        },

        computed: {
            options() {
                if (!this.config?.options) return [];

                return Object.entries(this.config.options).map(([key, value]) => {
                    return {
                        'value': typeof value === 'string' ? key : (value.key || key),
                        'label': value.label || value,
                        'icon': value.value || ''
                    };
                });
            },
        },

        methods: {
            update(value) {
                this.$emit('update:value', value);
            },
        },
    });
});
