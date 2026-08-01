export default {
	extends: Vue.options.components['button_group-fieldtype'],

	render: null,

	template: `
		<div class="button-group-fieldtype-wrapper">
			<div class="btn-group">
				<button class="btn px-2"
					v-for="(option, $index) in options"
					:key="$index"
					ref="button"
					type="button"
					:name="name"
					@click="updateSelectedOption(option.value)"
					:value="option.value"
					:disabled="isReadOnly"
					:class="{ 'active': value === option.value }"
					v-html="option.label || option.value"
				/>
			</div>
		</div>
	`,
};
