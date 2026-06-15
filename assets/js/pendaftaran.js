document.addEventListener('DOMContentLoaded', () => {
	const form = document.querySelector('[data-registration-form]');
	const printButton = document.querySelector('.js-print-receipt');

	if (!form) {
		if (printButton) {
			printButton.addEventListener('click', () => window.print());
		}
		return;
	}

	const panels       = Array.from(form.querySelectorAll('[data-step-panel]'));
	const dots         = Array.from(form.querySelectorAll('[data-step-dot]'));
	const nextButtons  = Array.from(form.querySelectorAll('[data-step-next]'));
	const prevButtons  = Array.from(form.querySelectorAll('[data-step-prev]'));
	const poliSelect   = form.querySelector('[data-poli-select]');
	const jaminanSelect  = form.querySelector('[data-jaminan-select]');
	const noJaminanWrap  = form.querySelector('[data-no-jaminan-wrap]');
	const noJaminanInput = form.querySelector('#no_jaminan');
	const tanggalInput   = form.querySelector('#tanggal_kunjungan');
	const sesiSelect     = form.querySelector('#sesi');
	const confirmNama    = form.querySelector('[data-confirm-nama]');
	const confirmPoli    = form.querySelector('[data-confirm-poli]');
	const confirmTanggal = form.querySelector('[data-confirm-tanggal]');
	const confirmSesi    = form.querySelector('[data-confirm-sesi]');
	const previewTitle   = form.querySelector('[data-preview-title]');
	const previewDesc    = form.querySelector('[data-preview-desc]');
	const previewDoctor  = form.querySelector('[data-preview-doctor]');
	const previewSchedule = form.querySelector('[data-preview-schedule]');
	const previewHours   = form.querySelector('[data-preview-hours]');
	const infoAntrian    = document.getElementById('infoAntrian');
	const baseUrl        = form.dataset.baseUrl || '';

	let currentStep = 0;
	let antrianTimer = null;

	const today = new Date();
	today.setHours(0, 0, 0, 0);
	if (tanggalInput) {
		const yyyy = today.getFullYear();
		const mm   = String(today.getMonth() + 1).padStart(2, '0');
		const dd   = String(today.getDate()).padStart(2, '0');
		tanggalInput.min = `${yyyy}-${mm}-${dd}`;
	}

	// ── Estimasi Antrian ──────────────────────────────────────────────────────
	function cekAntrian() {
		if (!infoAntrian) return;

		const poliId  = poliSelect?.value;
		const tanggal = tanggalInput?.value;
		const sesi    = sesiSelect?.value;

		if (!poliId || !tanggal || !sesi) {
			infoAntrian.hidden = true;
			return;
		}

		// Tampilkan loading
		infoAntrian.hidden = false;
		infoAntrian.className = 'info-antrian info-antrian-loading';
		infoAntrian.innerHTML =
			'<i class="bi bi-hourglass-split"></i> <span>Mengecek ketersediaan sesi...</span>';

		const url = `${baseUrl}/api/cek-antrian.php?poli_id=${encodeURIComponent(poliId)}&tanggal=${encodeURIComponent(tanggal)}&sesi=${encodeURIComponent(sesi)}`;

		fetch(url)
			.then(r => r.json())
			.then(data => {
				if (!data.success) {
					infoAntrian.hidden = true;
					return;
				}

				if (data.jumlah === 0) {
					infoAntrian.className = 'info-antrian info-antrian-ok';
					infoAntrian.innerHTML = `
						<span class="antrian-icon"><i class="bi bi-check-circle-fill"></i></span>
						<div>
							<strong>Sesi ini masih kosong!</strong>
							<p>Belum ada yang mendaftar. Anda akan mendapat nomor antrian <strong>#1</strong>.</p>
						</div>`;
				} else {
					infoAntrian.className = 'info-antrian info-antrian-warn';
					infoAntrian.innerHTML = `
						<span class="antrian-icon"><i class="bi bi-people-fill"></i></span>
						<div>
							<strong>${data.jumlah} orang sudah mendaftar di sesi ini</strong>
							<p>Estimasi nomor antrian Anda: <strong>#${data.antrian}</strong>.</p>
						</div>`;
				}
			})
			.catch(() => {
				infoAntrian.hidden = true;
			});
	}

	function scheduleCekAntrian() {
		clearTimeout(antrianTimer);
		antrianTimer = setTimeout(cekAntrian, 400);
	}

	if (tanggalInput) tanggalInput.addEventListener('change', scheduleCekAntrian);
	if (sesiSelect)   sesiSelect.addEventListener('change', scheduleCekAntrian);
	if (poliSelect)   poliSelect.addEventListener('change', scheduleCekAntrian);

	// ── Step navigation ───────────────────────────────────────────────────────
	function getVisibleFieldValue(selector) {
		const input = form.querySelector(selector);
		return input ? input.value.trim() : '';
	}

	function setStep(index) {
		currentStep = Math.max(0, Math.min(index, panels.length - 1));
		panels.forEach((panel, i) => {
			panel.classList.toggle('is-active', i === currentStep);
			panel.hidden = i !== currentStep;
		});
		dots.forEach((dot, i) => {
			dot.classList.toggle('is-active',   i <= currentStep);
			dot.classList.toggle('is-complete', i < currentStep);
		});
	}

	function validateStep(index) {
		const panel = panels[index];
		if (!panel) return true;

		const fields = Array.from(panel.querySelectorAll('input, select, textarea'));
		for (const field of fields) {
			if (field.disabled) continue;
			if (typeof field.checkValidity === 'function' && !field.checkValidity()) {
				field.reportValidity();
				return false;
			}
		}

		if (index === 0 && poliSelect && !poliSelect.value) {
			poliSelect.reportValidity();
			return false;
		}

		if (index === 2 && noJaminanInput && jaminanSelect &&
			jaminanSelect.value !== 'Umum' && !noJaminanInput.value.trim()) {
			noJaminanInput.setCustomValidity('Nomor jaminan sebaiknya diisi untuk jenis jaminan ini.');
			noJaminanInput.reportValidity();
			noJaminanInput.setCustomValidity('');
			return false;
		}

		return true;
	}

	function updateJaminanState() {
		if (!jaminanSelect || !noJaminanInput || !noJaminanWrap) return;
		const isUmum = jaminanSelect.value === 'Umum';
		noJaminanInput.required = !isUmum;
		noJaminanInput.placeholder = isUmum
			? 'Opsional untuk Umum'
			: `Wajib diisi untuk ${jaminanSelect.value}`;
		noJaminanWrap.classList.toggle('is-optional', isUmum);
	}

	function updatePreview() {
		if (!poliSelect) return;
		const opt = poliSelect.options[poliSelect.selectedIndex];
		if (!opt || !opt.value) {
			if (previewTitle)    previewTitle.textContent    = 'Pilih poli aktif terlebih dahulu';
			if (previewDesc)     previewDesc.textContent     = 'Informasi poli akan muncul setelah Anda memilih layanan.';
			if (previewDoctor)   previewDoctor.textContent   = '-';
			if (previewSchedule) previewSchedule.textContent = '-';
			if (previewHours)    previewHours.textContent    = '-';
			return;
		}
		if (previewTitle)    previewTitle.textContent    = opt.textContent.trim();
		if (previewDesc)     previewDesc.textContent     = opt.dataset.deskripsi || 'Layanan kesehatan yang tersedia.';
		if (previewDoctor)   previewDoctor.textContent   = opt.dataset.dokter   || '-';
		if (previewSchedule) previewSchedule.textContent = opt.dataset.jadwal   || '-';
		if (previewHours)    previewHours.textContent    = opt.dataset.jam      || '-';
	}

	function updateConfirmation() {
		if (confirmNama)    confirmNama.textContent    = getVisibleFieldValue('#nama_lengkap') || '-';
		if (confirmPoli && poliSelect)
			confirmPoli.textContent = poliSelect.options[poliSelect.selectedIndex]?.textContent.trim() || '-';
		if (confirmTanggal) confirmTanggal.textContent = getVisibleFieldValue('#tanggal_kunjungan') || '-';
		if (confirmSesi)    confirmSesi.textContent    = getVisibleFieldValue('#sesi') || '-';
	}

	nextButtons.forEach(btn => {
		btn.addEventListener('click', () => {
			if (!validateStep(currentStep)) return;
			updatePreview();
			updateConfirmation();
			setStep(currentStep + 1);
		});
	});

	prevButtons.forEach(btn => {
		btn.addEventListener('click', () => setStep(currentStep - 1));
	});

	if (poliSelect)    poliSelect.addEventListener('change', () => { updatePreview(); updateConfirmation(); });
	if (jaminanSelect) jaminanSelect.addEventListener('change', updateJaminanState);
	if (noJaminanInput) noJaminanInput.addEventListener('input', () => noJaminanInput.setCustomValidity(''));

	form.querySelectorAll('input, select, textarea').forEach(field => {
		field.addEventListener('input',  updateConfirmation);
		field.addEventListener('change', updateConfirmation);
	});

	form.addEventListener('submit', event => {
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
