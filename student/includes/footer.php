
		<!-- jQuery -->
		<script src="../assets/js/jquery-3.7.1.min.js" type="d220a6bb4a7797d6ac44761a-text/javascript"></script>
		
		<!-- Bootstrap Core JS -->
		<script src="../assets/js/bootstrap.bundle.min.js" type="d220a6bb4a7797d6ac44761a-text/javascript"></script>

		<!-- Sticky Sidebar JS -->
        <script src="../assets/plugins/theia-sticky-sidebar/ResizeSensor.js" type="d220a6bb4a7797d6ac44761a-text/javascript"></script>
        <script src="../assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js" type="d220a6bb4a7797d6ac44761a-text/javascript"></script>
		
		<!-- Student Sidebar JS -->
		<script data-cfasync="false" type="text/javascript">
			(function () {
				function ready(callback) {
					if (document.readyState === 'loading') {
						document.addEventListener('DOMContentLoaded', callback);
						return;
					}
					callback();
				}

				ready(function () {
					var body = document.body;
					var control = document.getElementById('studentSidebarControl');
					var sidebar = document.getElementById('studentSidebar');
					var toggleButton = document.querySelector('.student-sidebar-toggle');
					var backdrop = document.querySelector('[data-student-sidebar-close]');
					var navLinks = document.querySelectorAll('.student-sidebar-nav a');

					if (!body || !control || !sidebar) {
						return;
					}

					function syncSidebarState() {
						var isOpen = control.checked;
						body.classList.toggle('student-sidebar-open', isOpen);
						sidebar.classList.toggle('is-open', isOpen);
						if (backdrop) {
							backdrop.classList.toggle('is-visible', isOpen);
						}
						if (toggleButton) {
							toggleButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
						}
					}

					function closeSidebar() {
						control.checked = false;
						syncSidebarState();
					}

					window.StudentSidebarMenu = {
						open: function () {
							control.checked = true;
							syncSidebarState();
						},
						close: closeSidebar,
						toggle: function () {
							control.checked = !control.checked;
							syncSidebarState();
						}
					};

					control.addEventListener('change', syncSidebarState, false);

					if (toggleButton) {
						toggleButton.addEventListener('keydown', function (event) {
							if (event.key === 'Enter' || event.key === ' ' || event.keyCode === 13 || event.keyCode === 32) {
								event.preventDefault();
								window.StudentSidebarMenu.toggle();
							}
						}, false);
					}

					for (var i = 0; i < navLinks.length; i += 1) {
						navLinks[i].addEventListener('click', function () {
							if (window.innerWidth < 992) {
								closeSidebar();
							}
						}, false);
					}

					document.addEventListener('keydown', function (event) {
						if (event.key === 'Escape' || event.keyCode === 27) {
							closeSidebar();
						}
					}, false);

					syncSidebarState();
				});
			}());
		</script>
		<!-- Custom JS -->
		<script src="../assets/js/script.js" type="d220a6bb4a7797d6ac44761a-text/javascript"></script>
		
	<script src="../assets/js/rocket-loader.min.js" data-cf-settings="d220a6bb4a7797d6ac44761a-|49" defer></script></body>

</html>