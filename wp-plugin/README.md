# GEO Discovery WordPress Plugin

This folder contains the **GEO Discovery Landing Page** WordPress plugin. It registers a public route at `/geo/programmes/mba/broad` and renders a themed landing page using the active theme's header and footer.

## Installation (ZIP upload)
1. Zip the `geo-discovery` folder.
2. In WordPress, go to **Plugins → Add New → Upload Plugin**.
3. Upload the ZIP file, click **Install Now**, then **Activate**.
4. Visit **Settings → GEO Discovery** to configure the canonical MBA URL.

## Installation (folder upload)
1. Copy the `geo-discovery` folder into `wp-content/plugins/` on your server.
2. In WordPress, go to **Plugins → Installed Plugins**.
3. Activate **GEO Discovery Landing Page**.
4. Visit **Settings → GEO Discovery** to configure the canonical MBA URL.

## Usage
- Visit `/geo/programmes/mba/broad` to view the landing page.
- The page renders with the active theme’s header and footer.
- CTAs use the canonical MBA URL configured in **Settings → GEO Discovery**.
