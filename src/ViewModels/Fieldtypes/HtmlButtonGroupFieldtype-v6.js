import _ from "lodash";

export default {
	template: `
		<ui-button-group>
			<ui-button
				v-for="(option, $index) in options"
				ref="button"
				:disabled="config.disabled"
				:key="$index"
				:name="name"
				:read-only="isReadOnly"
				:text="option.label || option.value"
				:value="option.value"
				:variant="value == option.value ? 'pressed' : 'default'"
				@click="update($event.target.closest('button').value)"
				v-html="option.label || option.value"
			/>
		</ui-button-group>
	`,
	name: "HTMLButtonGroupFieldtype",
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
