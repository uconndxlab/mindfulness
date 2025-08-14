<form wire:submit.prevent="submit">
	@if ($successMessage)
		<div class="alert alert-success">{{ $successMessage }}</div>
	@endif

	<div class="mb-3">
		<label for="lw-subject" class="form-label fw-bold">Subject</label>
		<input type="text" class="form-control @error('subject') is-invalid @enderror" id="lw-subject" wire:model.blur="subject" wire:key="subject-field-{{ $formKey }}" placeholder="Examples: bug, library help,...">
		@error('subject')
			<div class="invalid-feedback fw-bold">{{ $message }}</div>
		@enderror
	</div>
	<div class="mb-3">
		<label for="lw-message" class="form-label fw-bold">Message</label>
		<textarea class="form-control @error('message') is-invalid @enderror" id="lw-message" wire:model.blur="message" wire:key="message-field-{{ $formKey }}" rows="4" placeholder="Describe your question or issue..."></textarea>
		@error('message')
			<div class="invalid-feedback fw-bold">{{ $message }}</div>
		@enderror
	</div>
	<div class="text-center">
		<button type="submit" class="btn btn-primary">Submit</button>
	</div>
</form>


