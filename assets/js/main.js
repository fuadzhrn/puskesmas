const menuToggle = document.getElementById('menuToggle');
const siteNav = document.getElementById('siteNav');

function openNav() {
	siteNav.classList.add('show');
	menuToggle.setAttribute('aria-expanded', 'true');
	siteNav.setAttribute('aria-hidden', 'false');
	menuToggle.classList.add('is-open');
}

function closeNav() {
	siteNav.classList.remove('show');
	menuToggle.setAttribute('aria-expanded', 'false');
	siteNav.setAttribute('aria-hidden', 'true');
	menuToggle.classList.remove('is-open');
}

if (menuToggle && siteNav) {
	menuToggle.addEventListener('click', () => {
		siteNav.classList.contains('show') ? closeNav() : openNav();
	});

	document.addEventListener('click', (e) => {
		if (!menuToggle.contains(e.target) && !siteNav.contains(e.target)) {
			closeNav();
		}
	});

	siteNav.querySelectorAll('a').forEach(link => {
		link.addEventListener('click', closeNav);
	});
}

function cetakBukti(btn) {
	const card = btn.closest('.result-card');
	document.body.classList.add('printing');
	card.classList.add('print-target');
	window.print();
	window.addEventListener('afterprint', function cleanup() {
		document.body.classList.remove('printing');
		card.classList.remove('print-target');
		window.removeEventListener('afterprint', cleanup);
	});
}
