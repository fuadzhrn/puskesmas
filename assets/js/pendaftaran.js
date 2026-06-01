document.addEventListener('DOMContentLoaded', () => {
	const form = document.querySelector('[data-registration-form]');
	const printButton = document.querySelector('.js-print-receipt');

	if (!form) {
		if (printButton) {
			printButton.addEventListener('click', () => window.print());
		}
		return;
	}

	const panels = Array.from(form.querySelectorAll('[data-step-panel]'));
	const dots = Array.from(form.querySelectorAll('[data-step-dot]'));
	const nextButtons = Array.from(form.querySelectorAll('[data-step-next]'));
	const prevButtons = Array.from(form.querySelectorAll('[data-step-prev]'));
	const poliSelect = form.querySelector('[data-poli-select]');
	const jaminanSelect = form.querySelector('[data-jaminan-select]');
	const noJaminanWrap = form.querySelector('[data-no-jaminan-wrap]');
	const noJaminanInput = form.querySelector('#no_jaminan');
	const tanggalInput = form.querySelector('#tanggal_kunjungan');
	const confirmNama = form.querySelector('[data-confirm-nama]');
	const confirmPoli = form.querySelector('[data-confirm-poli]');
	const confirmTanggal = form.querySelector('[data-confirm-tanggal]');
	const confirmSesi = form.querySelector('[data-confirm-sesi]');
	const previewTitle = form.querySelector('[data-preview-title]');
	const previewDesc = form.querySelector('[data-preview-desc]');
	const previewDoctor = form.querySelector('[data-preview-doctor]');
	const previewSchedule = form.querySelector('[data-preview-schedule]');
	const previewHours = form.querySelector('[data-preview-hours]');

	let currentStep = 0;

	const today = new Date();
	today.setHours(0, 0, 0, 0);
	if (tanggalInput) {
		const yyyy = today.getFullYear();
		const mm = String(today.getMonth() + 1).padStart(2, '0');
		const dd = String(today.getDate()).padStart(2, '0');
		tanggalInput.min = `${yyyy}-${mm}-${dd}`;
	}

	function getVisibleFieldValue(selector) {
		const input = form.querySelector(selector);
		return input ? input.value.trim() : '';
	}

	function setStep(index) {
		currentStep = Math.max(0, Math.min(index, panels.length - 1));
		panels.forEach((panel, panelIndex) => {
			panel.classList.toggle('is-active', panelIndex === currentStep);
			panel.hidden = panelIndex !== currentStep;
		});

		dots.forEach((dot, dotIndex) => {
			dot.classList.toggle('is-active', dotIndex <= currentStep);
			dot.classList.toggle('is-complete', dotIndex < currentStep);
		});
	}

	function validateStep(index) {
		const panel = panels[index];
		if (!panel) {
			return true;
		}

		const fields = Array.from(panel.querySelectorAll('input, select, textarea'));
		for (const field of fields) {
			if (field.disabled) {
				continue;
			}

			if (typeof field.checkValidity === 'function' && !field.checkValidity()) {
				field.reportValidity();
				return false;
			}
		}

		if (index === 0 && poliSelect && !poliSelect.value) {
			poliSelect.reportValidity();
			return false;
		}

		if (index === 2 && noJaminanInput && jaminanSelect && jaminanSelect.value !== 'Umum' && !noJaminanInput.value.trim()) {
			noJaminanInput.setCustomValidity('Nomor jaminan sebaiknya diisi untuk jenis jaminan ini.');
			noJaminanInput.reportValidity();
			noJaminanInput.setCustomValidity('');
			return false;
		}

		return true;
	}

	function updateJaminanState() {
		if (!jaminanSelect || !noJaminanInput || !noJaminanWrap) {
			return;
		}

		const isUmum = jaminanSelect.value === 'Umum';
		noJaminanInput.required = !isUmum;
		noJaminanInput.placeholder = isUmum ? 'Opsional untuk Umum' : `Wajib diisi untuk ${jaminanSelect.value}`;
		noJaminanWrap.classList.toggle('is-optional', isUmum);
	}

	function updatePreview() {
		if (!poliSelect) {
			return;
		}

		const selectedOption = poliSelect.options[poliSelect.selectedIndex];
		if (!selectedOption || !selectedOption.value) {
			if (previewTitle) previewTitle.textContent = 'Pilih poli aktif terlebih dahulu';
			if (previewDesc) previewDesc.textContent = 'Informasi poli akan muncul setelah Anda memilih layanan.';
			if (previewDoctor) previewDoctor.textContent = '-';
			if (previewSchedule) previewSchedule.textContent = '-';
			if (previewHours) previewHours.textContent = '-';
			return;
		}

		if (previewTitle) previewTitle.textContent = selectedOption.textContent.trim();
		if (previewDesc) previewDesc.textContent = selectedOption.dataset.deskripsi || 'Layanan kesehatan yang tersedia untuk pendaftaran pasien.';
		if (previewDoctor) previewDoctor.textContent = selectedOption.dataset.dokter || '-';
		if (previewSchedule) previewSchedule.textContent = selectedOption.dataset.jadwal || '-';
		if (previewHours) previewHours.textContent = selectedOption.dataset.jam || '-';
	}

	function updateConfirmation() {
		if (confirmNama) confirmNama.textContent = getVisibleFieldValue('#nama_lengkap') || '-';
		if (confirmPoli && poliSelect) {
			confirmPoli.textContent = poliSelect.options[poliSelect.selectedIndex]?.textContent.trim() || '-';
		}
		if (confirmTanggal) confirmTanggal.textContent = getVisibleFieldValue('#tanggal_kunjungan') || '-';
		if (confirmSesi) confirmSesi.textContent = getVisibleFieldValue('#sesi') || '-';
	}

	nextButtons.forEach((button) => {
		button.addEventListener('click', () => {
			if (!validateStep(currentStep)) {
				return;
			}

			updatePreview();
			updateConfirmation();
			setStep(currentStep + 1);
		});
	});

	prevButtons.forEach((button) => {
		button.addEventListener('click', () => {
			setStep(currentStep - 1);
		});
	});

	if (poliSelect) {
		poliSelect.addEventListener('change', () => {
			updatePreview();
			updateConfirmation();
		});
	}

	if (jaminanSelect) {
		jaminanSelect.addEventListener('change', updateJaminanState);
	}

	if (noJaminanInput) {
		noJaminanInput.addEventListener('input', () => {
			noJaminanInput.setCustomValidity('');
		});
	}

	form.querySelectorAll('input, select, textarea').forEach((field) => {
		field.addEventListener('input', updateConfirmation);
		field.addEventListener('change', updateConfirmation);
	});

	form.addEventListener('submit', (event) => {
		if (!validateStep(currentStep)) {
			event.preventDefault();
			return;
		}
		updateConfirmation();
	});

	if (printButton) {
		printButton.addEventListener('click', () => window.print());
	}

	updateJaminanState();
	updatePreview();
	updateConfirmation();
	setStep(0);
});
