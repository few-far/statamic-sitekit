@extends('statamic::layout')
@section('title', __('Migrations'))

@section('content')
	<div
		class="card p-6"
		x-data="{
			'url': {{ Js::from($url) }},
			'submitting': false,
			'output': null,
		}"
	>
		<h2>Dry run</h2>

		<pre class="rounded shadow-inner border bg-gray-100 my-2 min-h-40">
			{{ $dryrun }}
		</pre>

		<button
			class="btn"
			x-on:click="() => {
				if (submitting) {
					return;
				}

				submitting = true;
				output = null;

				Vue.prototype.$axios.post(url).then(response => {
					submitting = false;
					output = response.data.data.output;
				});
			}"
		>
			Run migrations
		</button>

		<div x-show="output" class="mt-4" x-cloak>
			<h2>Output</h2>

			<pre class="rounded shadow-inner border bg-gray-100 my-2 min-h-40" x-text="output"></pre>
		</div>
	</div>
@endsection

@section('scripts')
	<script>
		document.addEventListener('cp:init', () => {
			//
		});
	</script>
@endsection
