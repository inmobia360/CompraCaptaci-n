# UX registration and pricing changes - 2026-06-29

## Scope

Applied approved CRO/UX changes to the active WordPress theme in `stable-1.5.1/captacion-app`.

## Changes

- Main CTA copy changed to `Probar gratis`, with the trust badge `Sin tarjeta · 3 accesos`.
- Mobile navigation now shows `Probar gratis` before `Acceder`.
- The former pending testimonials card was replaced with three benefit cards focused on controlled access, connected demand and traceable follow-up.
- Registration forms were simplified to email, password and privacy acceptance.
- Name and phone are now completed later from the professional profile.
- The REST registration endpoint now accepts optional phone and generates a provisional display name from the email when name is omitted.
- Google and Apple OAuth UI buttons were added as plugin-ready links for a social-login plugin such as `Sign In With Socials`.
- Pricing now includes a monthly/annual UI toggle, with annual prices of `290 €/año` for Professional and `490 €/año` for Premium.
- Admin settings now include separate annual Stripe Payment Links for Professional and Premium.
- The pricing CTA uses the selected billing cycle and falls back to the monthly link if an annual link is not configured.
- Social login buttons are hidden behind the `social_login_enabled` admin setting until a compatible plugin is installed and configured.
- Typography loading now includes Inter 800 and 900, invalid font weights were normalized, and amber contrast was updated.

## Notes

- OAuth backend is intentionally not custom-built in the theme. Enable the social login setting only after a WordPress social-login plugin handles the provider flow.
- Annual Stripe checkout requires configuring the new annual Payment Link fields in the Captacion.app admin page.
