<form wire:submit.prevent="update" class="mt-4">
	@if ($successMessage)
		<div class="alert alert-success">{{ $successMessage }}</div>
	@endif

	<div class="form-group mb-3">
		<label class="fw-bold" for="lw-name">Change Displayed Name</label>
		<input id="lw-name" type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name">
		@error('name')
			<div class="invalid-feedback fw-bold">{{ $message }}</div>
		@enderror
	</div>

	<div class="form-group mb-3">
		<label class="fw-bold" for="lw-password">Change Password</label><br>
		<small>Enter your new password below (Must contain at least 8 characters, one uppercase letter, one lowercase letter, and one number)</small>
		<input id="lw-password" type="password" class="form-control @error('password') is-invalid @enderror" wire:model="password" wire:key="password-field-{{ $formKey }}">
		@error('password')
			<div class="invalid-feedback fw-bold">{{ $message }}</div>
		@enderror
	</div>

	<div class="form-group mb-3">
		<label class="fw-bold" for="lw-oldPass">Enter Old Password to Confirm Changes</label><br>
		<small>Enter your current password to verify your identity.</small>
		<input id="lw-oldPass" type="password" class="form-control @error('oldPass') is-invalid @enderror" wire:model="oldPass" wire:key="oldPass-field-{{ $formKey }}">
		@error('oldPass')
			<div class="invalid-feedback fw-bold">{{ $message }}</div>
		@enderror
	</div>

	<div class="text-center">
		<div class="form-group">
			<button type="submit" class="btn btn-primary">SAVE</button>
		</div>
	</div>
</form>
