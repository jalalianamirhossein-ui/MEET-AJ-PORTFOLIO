<?php
return ['temporary_file_upload' => ['disk' => 'local', 'rules' => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp'], 'directory' => 'livewire-tmp']];
