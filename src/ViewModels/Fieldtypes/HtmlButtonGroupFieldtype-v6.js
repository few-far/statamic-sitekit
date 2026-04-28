import _ from "lodash";

export default {
	template: `
		<div class="button-group-fieldtype-wrapper">
			<ui-button-group class="dark:fill-white">
				<ui-button
					class="btn px-2"
					v-for="(option, $index) in options"
					:key="$index"
					ref="button"
					:name="name"
					@click="update($event.target.closest('button').value)"
					:value="option.value"
					:variant="option.value === value  ? 'pressed' : ''"
					:disabled="isReadOnly"
					:class="{'active': value === option.value}"
					v-html="option.label || option.value"
				/>
			</ui-button-group>
		</div>
	`,
	name: "HTMLButtonGroupFieldtype",

	props: {
		value: String,
		config: Object,
		name: String,
		isReadOnly: Boolean,
	},

	computed: {
		options() {
            return _.map(this.config.options, (value, key) => {
                return {
                    'value': typeof value === 'string' ? key : value.key,
                    'label': value.value || value
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
