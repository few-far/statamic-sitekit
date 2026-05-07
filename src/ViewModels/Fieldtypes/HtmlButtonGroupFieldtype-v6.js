import _ from "lodash";

export default {
	template: `
		<ui-button-group class="~fill-current">
			<ui-button
				class="px-2"
				v-for="(option, $index) in options"
				:key="$index"
				ref="button"
				:name="name"
				@click="update($event.target.closest('button').value)"
				:value="option.value"
				:disabled="isReadOnly"
				:variant="value === option.value ? 'pressed' : 'default'"
				v-html="option.label || option.value"
			/>
		</ui-button-group>
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
