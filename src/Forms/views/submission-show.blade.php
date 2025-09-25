@extends('statamic::layout')
@section('title', $form->get('title') . ' – ' . __('Submissions') . ' – ' . $submission->id)

@section('content')
	@include('statamic::partials.breadcrumb', [
		'url' => cp_route('submissions.index', $form->id()),
		'title' => $form->get('title') . ' – Submissions',
	])

	<header class="mb-3">
		<div class="flex items-center">
			<h1 class="flex-1">
				Submission #{{ $submission->id }}
			</h1>

			<button
				class="btn btn-small"
				data-action="{{ cp_route('submissions/notifications.store', $submission->id) }}"
				x-on:click="() => {
					if (! confirm('This will send the nofication emails again, are you sure?')) {
						return;
					}

					Statamic.$app.$axios.post($el.dataset.action)
						.then(() => new Promise(resolve => setTimeout(resolve, 500)))
						.then(() => Statamic.$toast.success('Notifications sent'))
				}"
			>
				Resend notification
			</button>
		</div>
	</header>

	<div class="card p-6 py-5 text-sm grid gap-1">
		<div class="font-semibold flex gap-2">
			<div class="w-1/4">Field</div>
			<div class="flex-grow">Value</div>
		</div>

		@foreach ($submission->values as $field)
			<div class="pt-2 flex gap-2">
				<div class="w-1/4">
					<div class="font-semibold">
						@isset ($field['heading'])
							{{ $field['heading'] }}
						@else
							{{ $field['label'] }}
							<div class="text-3xs font-normal font-mono text-gray-700">{{ $field['name'] }}</div>
						@endisset
					</div>
				</div>

				<div class="flex-grow">
					@isset($field['heading'])
					@else
						{{ collect($field['value'])->implode(', ') }}</td>
					@endisset
				</div>
			</div>
		@endforeach
	</div>

	<div class="mt-4 card p-6 py-5 text-sm grid gap-1">
		<div class="font-semibold flex gap-2">
			<div class="w-1/4">Meta</div>
			<div class="flex-grow">Value</div>
		</div>

		@foreach ($meta as $row)
			<div class="pt-2 flex gap-2">
				<div class="w-1/4">{{ $row['name'] }}</div>
				<div class="flex-grow">{{ $row['value'] }}</div>
			</div>
		@endforeach
	</div>
@endsection
