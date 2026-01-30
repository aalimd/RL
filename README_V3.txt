INSTALLATION INSTRUCTIONS (V3 - Fixed 500 Errors)

1. **Clean Installation Required**:
   - Please DELETE all existing files on your hosting file manager (except `public_html` folder itself).
   - Please DROP all tables in your database (or use a fresh database).

2. **Upload & Extract**:
   - Upload `RecomSystem_Full_Package_v3_Fixed.zip` to your hosting.
   - Extract the contents.

3. **Install**:
   - Open your browser and go to: `https://your-domain.com/install`
   - Follow the wizard steps.
   - The installer will automatically create the database tables with the CORRECT schema (longText for form_data) to avoid the 500 error.

4. **Post-Install**:
   - Delete the `/install` folder after successful installation.

TROUBLESHOOTING:
- If you see a 500 error immediately, verify permissions on `backend/storage` are 775 or 777.
- If you see "Constraint violation" again, it means the old database tables were NOT dropped. You MUST drop the `requests` table for the fix to apply.
