const cptNav = document.querySelector('#cpt-nav');
const cptNavSubmenus = document.querySelectorAll('#cpt-nav .menu > .menu-item-has-children');

if (cptNav && cptNavSubmenus) {
	window.addEventListener('resize', preventOffscreenSubmenus);
	wp.domReady(preventOffscreenSubmenus);
	
	function preventOffscreenSubmenus() {
		let containerRect = cptNav.getBoundingClientRect();
	
		cptNavSubmenus.forEach(menu => {
			menu.classList.remove('align-submenu-right');
			let menuRect = menu.getBoundingClientRect();
			let menuCenter = menuRect.right - ((menuRect.right - menuRect.left) / 2);
	
			// 115px is half the max width of a submenu (230px). See /assets/scss/_menus.scss
			if ((menuCenter + 115) > containerRect.right) {
				menu.classList.add('align-submenu-right');
			}
		});
	}
}	

