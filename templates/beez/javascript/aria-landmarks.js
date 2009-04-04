function setRoleAttribute(id, rolevalue) {
	if(document.getElementById(id)) {
		document.getElementById(id).setAttribute("role", rolevalue);
	}
}
function setAriaRoleElementsById() {
	setRoleAttribute("header", "banner");
	setRoleAttribute("main", "main");
	setRoleAttribute("main2", "main");
	setRoleAttribute("right", "complementary");
	setRoleAttribute("footer", "contentinfo");
	setRoleAttribute("left", "navigation");
	setRoleAttribute("searchbox", "search");
}
window.addEvent('domready', function() { setAriaRoleElementsById(); });
