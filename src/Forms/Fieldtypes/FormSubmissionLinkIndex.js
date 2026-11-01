document.addEventListener('alpine:init', function () {
	Statamic.$components.register('form_submissions_link-fieldtype-index', {
		mixins: [ window.IndexFieldtype ],
		template: `
			<a :href="value.action" class="btn btn-xs">View submissions</a>
		`,
	});
});
