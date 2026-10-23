// Remove loading overlay on location-dependent fields.
// The actual state → LGA/zone cascading select is handled by the
// API-driven toggleLGA() in public/frontend/js/lga.js (loaded separately
// via <script src> on the post-ad/edit-ad pages), not by this module.
[...document.querySelectorAll(".input-location-dependant")].forEach(element =>
  element.classList.toggle("d-none")
);
