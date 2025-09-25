@extends('statamic::layout')
@section('title', __('Redirects'))

@section('content')
	@include('statamic::partials.breadcrumb', [
		'url' => cp_route('redirects'),
		'title' => __('Redirects')
	])

	<redirect-publish-form
        :form='@json($form)'
    ></redirect-publish-form>
@endsection

@section('scripts')
	<script>
		document.addEventListener('cp:init', () => {
			Statamic.$components.register('redirect-publish-form', {
				template: `
					<publish-form v-bind="form" @saved="saved" />
				`,

				props: {
					form: Object,
				},

				methods: {
					saved(response) {
						const redirect = response?.data?.redirect;

						if (redirect) {
							this.$nextTick(() => window.location = redirect);
						}
					},
				},
			});
		});
	</script>
@endsection
