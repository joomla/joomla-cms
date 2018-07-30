Joomla = window.Joomla || {}, (t, s) => {
	"use strict";

	const a = (t, a) => {
		const e;
		["matches", "msMatchesSelector"].some((t) => {
			return "function" == typeof s.body[t] && (e = t, !0)
		});
		for (const c; t;) {
			if ((c = t.parentElement) && c[e](a)) return c;
			t = c
		}
		return null
	}

	s.addEventListener("joomla:updated", (t) => {
		for (const e = t && t.target ? t.target : s, c = e.querySelectorAll(".btn-group"), d = 0; d < c.length; d++) for (const l = c[d].querySelectorAll("label"), i = 0; i < l.length; i++) l[i].classList.add("btn"), i % 2 == 1 ? l[i].classList.add("btn-outline-danger") : l[i].classList.add("btn-outline-success");
		const n = e.querySelector(".btn-group label:not(.active)");
		n && n.addEventListener("click", (t) => {
			const e = s.getElementById(t.target.getAttribute("for"));
			if ("checked" !== e.getAttribute("checked")) {
				const c = a(t.target, ".btn-group").querySelector("label");
				c.classList.remove("active"), c.classList.remove("btn-success"), c.classList.remove("btn-danger"), c.classList.remove("btn-primary"), a(c, ".btn-group").classList.contains("btn-group-reversed") ? (c.classList.contains("btn") || c.classList.add("btn"), "" === e.value ? (c.classList.add("active"), c.classList.add("btn"), c.classList.add("btn-outline-primary")) : 0 === e.value ? (c.classList.add("active"), c.classList.add("btn"), c.classList.add("btn-outline-success")) : (c.classList.add("active"), c.classList.add("btn"), c.classList.add("btn-outline-danger"))) : "" === e.value ? (c.classList.add("active"), c.classList.add("btn"), c.classList.add("btn-outline-primary")) : 0 === e.value ? (c.classList.add("active"), c.classList.add("btn"), c.classList.add("btn-outline-danger")) : (c.classList.add("active"), c.classList.add("btn"), c.classList.add("btn-outline-success")), e.setAttribute("checked", !0)
			}
		});
		for (const r = e.querySelectorAll(".btn-group input[checked=checked]"), o = (d = 0, r.length); o > d; d++) {
			const u = r[d], L = u.id, b = e.querySelector("label[for=" + L + "]");
			u.parentNode.parentNode.classList.contains("btn-group-reversed") ? "" === u.value ? (b.classList.add("active"), b.classList.add("btn"), b.classList.add("btn-outline-primary")) : 0 === u.value ? (b.classList.add("active"), b.classList.add("btn"), b.classList.add("btn-outline-success")) : (b.classList.add("active"), b.classList.add("btn"), b.classList.add("btn-outline-danger")) : "" === u.value ? (b.classList.add("active"), b.classList.add("btn-outline-primary")) : 0 === u.value ? (b.classList.add("active"), b.classList.add("btn"), b.classList.add("btn-outline-danger")) : (b.classList.add("active"), b.classList.add("btn"), b.classList.add("btn-outline-success"))
		}
	})
}(Joomla, document);