# NicheClassify Framework – TODO

This checklist outlines key steps to finalize and polish the MVP for public use.

---

## ✅ Completed

- [x] Plugin scaffold and loader
- [x] Custom post type (`nc_listing`)
- [x] Taxonomies: category, location, type
- [x] Field schema manager (`NC_Field_Manager`)
- [x] Repeatable/group field support
- [x] Field renderers: text, select, textarea, checkbox, media, gallery
- [x] Admin metaboxes & REST support
- [x] Frontend submission form (`NC_Form_Handler`)
- [x] Gallery upload and field saving
- [x] Directory renderer (`NC_Directory_Renderer`)
- [x] Shortcode `[nc_directory]` with filtering & pagination
- [x] Sort by taxonomy and custom meta
- [x] Single listing template system (`single-nc_listing.php`)
- [x] Modular template partials (`parts/`, `directory/`)
- [x] Contact form in listing
- [x] User dashboard (`NC_User_Dashboard`)
- [x] Shortcode `[nc_dashboard]` with delete/edit

---

## 🔜 In Progress / Next Steps

- [ ] JS for media/gallery selector
- [ ] JS for repeatable group fields
- [ ] Add nonce/security checks (submission, deletion, contact)
- [ ] Minimal CSS styling (forms, listings, dashboard)
- [ ] Fallback templates and graceful UI messages
- [ ] Prepare translation strings (`__()`/`_e()`) 
- [ ] Settings page (optional)

---

## 🧩 Extension Roadmap

- [ ] Freemius integration for licensing/Pro features
- [ ] Saved searches & email alerts
- [ ] Role-based listing permissions
- [ ] Search by location radius (map integration)
- [ ] REST API support for listing management
- [ ] Developer hooks & custom field registration

---

## 🧰 Developer Tools (Optional)

- [ ] WP-CLI or admin generator for new niche plugins
- [ ] Field inspector for REST/debug views
