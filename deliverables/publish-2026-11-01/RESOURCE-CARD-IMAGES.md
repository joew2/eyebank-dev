# Replace the resource-card photo with supplied desktop/mobile versions

1. Deactivate the earlier **Custom Resource Photo Framing CWS** plugin.
2. Install **15-custom-resource-card-images-1.0.0.zip** as a new plugin and activate **Custom Resource Card Images CWS**.
3. Open **Tools → Resource Card Images CWS**.
4. Check the backup agreement and click **Process all updates**. Keep the tab open.
5. Clear the site/CDN cache and review desktop and phone layouts.

The ZIP contains both supplied JPEGs and automatically imports them into the Media Library. It removes the previous sizing/crop-position CSS and replaces only the recipients/families Resources card with the appropriate image for each device. There is no separate image upload and no theme ZIP update.

The provided image proportions are preserved rather than cropped again; the mobile card follows the portrait image's height. The heading/link and other page sections remain intact. After success, this migration plugin may be deactivated because the imported files, responsive markup and styling remain in WordPress.

Already applied and verified locally. No online changes were made by building this ZIP.
