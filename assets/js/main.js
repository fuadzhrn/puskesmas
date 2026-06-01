const menuToggle = document.getElementById('menuToggle');
const siteNav = document.getElementById('siteNav');

if (menuToggle && siteNav) {
	menuToggle.addEventListener('click', () => {
		siteNav.classList.toggle('show');
	});
}
