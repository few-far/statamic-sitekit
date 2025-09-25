@extends('statamic::layout')
@section('title', $form->get('title') . ' – ' . __('Submissions'))

@section('content')
	@include('statamic::partials.breadcrumb', [
		'url' => $form->editUrl(),
		'title' => $form->get('title')
	])

	<header class="mb-3">
		<div class="flex items-center">
			<h1 class="flex-1">
				{{ __('Submissions') }}
			</h1>

			{{-- <a
				class="btn-primary"
				:href="create_url"
				v-text="__('Export submissions')"
			/> --}}
		</div>
	</header>

	<div class="card p-6 py-5 text-sm grid gap-1">
		<div class="font-semibold flex gap-2">
			<div class="w-1/4">Submission</div>
			<div class="w-1/2">Email</div>
			<div class="w-1/4"></div>
		</div>

		@foreach ($submissions as $submission)
			<div class="border-t mt-1 pt-2 border-gray-300 flex gap-2">
				<div class="w-1/4">
					<a href="{{ cp_route('submissions.show', [$form->id(), $submission->id]) }}">
						{{ $submission->created_at->toDateTimeString() }}
					</a>
				</div>

				<div class="w-1/2">
					{{ $submission->email }}
				</div>

				<div class="w-1/4" style="text-align: right;">
					<a
						href="{{ cp_route('submissions.show', [$form->id(), $submission->id]) }}"
						class="ml-auto"
					>
						View details
					</a>
				</div>
			</div>
		@endforeach
	</div>

	<div class="mt-4">
		{{ $submissions->links('pagination::simple-tailwind') }}
	</div>
@endsection
