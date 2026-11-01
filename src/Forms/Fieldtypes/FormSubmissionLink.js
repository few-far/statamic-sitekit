document.addEventListener('alpine:init', function () {
	Statamic.$components.register('form_submissions_link-fieldtype', {
		mixins: [ window.Fieldtype ],
		template: `
			<a :href="meta.action" class="text-sm text-blue">View submissions</a>
		`
	});
});
