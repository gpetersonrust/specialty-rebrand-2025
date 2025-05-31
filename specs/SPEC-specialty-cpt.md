
# SPEC.md: Specialty Post Type & Metabox Management System

## 📌 Purpose

To manage hierarchical medical specialties as Custom Post Types with fine-grained control over:
- Physician assignments (with tier-based sorting)
- Display label customization
- Admin-only drag-and-drop tier ordering
- Frontend visibility toggles

This system replaces taxonomy-only specialty management with a richer CPT-based model.

---

## 🧱 1. Custom Post Type: `specialty`

### Registration (`Specialty_Rebrand_Specialty_CPT`)
- **Post Type Name:** `specialty`
- **Menu Label:** “Specialties”
- **Hierarchical:** `false` (hierarchy handled via metadata and sorting logic)
- **Supports:** `title`, `editor`, `page-attributes`
- **Visibility:**
  - `public`: `true`
  - `show_ui`: `true`
  - `has_archive`: `false`
  - `show_in_rest`: `true`
- **Capability Type:** `post`
- **Position:** `25`

### Hook Registration
- Via `define_hooks($loader)`:
  ```php
  $loader->add_action('init', $this, 'register_post_type');
  ```

---

## 🧩 2. Metaboxes (`Specialty_Rebrand_Specialty_Metabox`)

### Hook Registration
All metaboxes and save logic must be bound via:
```php
$loader->add_action('add_meta_boxes', $this, 'register_metaboxes');
$loader->add_action('save_post_specialty', $this, 'save_metaboxes');
$loader->add_action('admin_enqueue_scripts', $this, 'enqueue_admin_assets');
```

---

### A. 🔖 Display Label Metabox

- **Meta Key:** `_specialty_display_label`
- **Type:** `text`
- **UI:** Simple input field to override the frontend label.

---

### B. 🩺 Physician Assignment & Tier Sorting

#### Structure
- Sortable, tier-grouped interface to assign physicians to a specialty.
- Supports drag-and-drop reordering *within* and *across* tiers.
- Physician list sourced from `physician` post type.

#### Fields:
- **Meta Key (per tier):** `_specialty_tier_order_{slug}`
  - Example: `_specialty_tier_order_sports` → `[33, 17, 10]`
- **Tier Metadata:**
  - **Meta Key:** `_specialty_tiers`
  - Format:
    ```json
    [
      { "slug": "sports", "label": "Sports", "sort_order": 1 },
      { "slug": "specialists", "label": "Specialists", "sort_order": 2 }
    ]
    ```

---

### C. 📃 Post Attachments (optional content blocks)

- **Meta Key:** `_specialty_content_blocks`
- **Type:** Post object multi-selector
- **Use Case:** Attach arbitrary post IDs to display alongside specialty

---

### D. 👁️ Visibility Toggle

- **Meta Key:** `_specialty_is_visible`
- **Type:** `checkbox`
- **Label:** "Show this specialty publicly"
- **Default:** true

---

## 🔧 3. Admin JavaScript

### Responsibilities
- Drag-and-drop for:
  - Tier reordering
  - Physician reordering within tiers
- Real-time updates (via AJAX or local state on save)
- Dynamic tier creation UI
- Integration with WP nonce & post save flow

### Hook
```php
$loader->add_action('admin_enqueue_scripts', $this, 'enqueue_admin_assets');
```

### Asset Suggestions
- Use `wp_enqueue_script('jquery-ui-sortable')`
- Target only `post_type=specialty` edit screens
- Save order using either:
  - Local JSON -> `save_post`
  - AJAX endpoint (`wp_ajax_save_specialty_order`)

---

## 🛡️ 4. Data & Validation Rules

- Ensure physicians aren’t assigned twice to the same tier
- Validate tier slugs (alphanumeric with dashes)
- Prevent save if `specialty_tiers` is malformed
- Sanitize and normalize post IDs and strings

---

## 🚀 5. Extensibility & REST API (Optional Phase 2)

Later enhancements could include:
- REST API endpoints for reading/writing physician-tier mappings
- Import/export support
- Inline tier management UI (edit/add/delete)
- Tier label translation fields (for multilingual support)

---

## 🔄 6. Class Naming & Hook Integration

All classes must:

- Be named using `Specialty_Rebrand_` prefix.
- Use a `define_hooks(Specialty_Rebrand_Loader $loader)` method.
- Register all `add_action`/`add_filter` hooks through the loader.

**Example:**
```php
class Specialty_Rebrand_Specialty_CPT {
    public function define_hooks($loader) {
        $loader->add_action('init', $this, 'register_post_type');
    }
}
```
