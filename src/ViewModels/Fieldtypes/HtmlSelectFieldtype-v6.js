import _ from "lodash";

export default {
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
				<div class="flex items-center">
					<span v-text="option.label" />
					<span v-html="option.icon" />
				</div>
			</template>

			<template #option="option">
				<div
					class="flex items-center"
				>
					<span v-text="option.label" />
					<span v-html="option.icon" />
				</div>
			</template>
		</ui-combobox>
	`,
	name: "HTMLSelectFieldtype",

	props: {
		value: String,
		config: Object,
		name: String,
	},

	computed: {
		options() {
            return _.map(this.config.options, (value, key) => {
                return {
                    'value': typeof value === 'string' ? key : value.key,
                    'label': value.label || value,
					'icon': value.value
                };
            });
        },
	},

	methods: {
		update(value) {
			this.$emit('update:value', value);
		},
	},
};
