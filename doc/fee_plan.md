Fee Plan Documentation
===================================

## Context

When saving API keys through the `SettingsService::saveWithNotification()` method, if new API keys are detected (via `hasNewKey()`), the system automatically fetches and saves fee plans from the Alma API into PrestaShop's `ps_configuration` table.

### Fee Plan Initialization Process

1. **API Key Validation**: When new API keys are submitted (not obscured values), the system:
   - Validates the API keys via `AuthenticationService::isValidKeys()`
   - Verifies that both test and live keys belong to the same merchant
   - Automatically switches the mode if the current mode doesn't match the provided keys

2. **Fee Plan Retrieval**: Upon successful key validation:
   - Fee plans are fetched from the Alma API using `FeePlansProvider::getFeePlanList()`
   - Data is processed through `FeePlansService::fieldsToSaveFromApi()` which extracts:
     - Installment count, deferred days, and deferred months
     - State (enabled/disabled) - all plans are disabled by default except P3X
     - Minimum and maximum amounts from the API
     - Sort order incremented based on product type (Pay now, PnX, Credit, Pay Later)

3. **Data Storage**: All fee plans are saved in a single configuration key called `ALMA_FEE_PLAN_LIST` as a JSON-encoded array with the format:
   ```json
   {
     "general_{installments}_{deferred_days}_{deferred_months}": {
       "state": "0|1",
       "min_amount": "10000",
       "max_amount": "200000",
       "sort_order": "5"
     }
   }
   ```

4. **Configuration Page Loading**: When the configuration page loads:
   - Fee plan data is retrieved from the API
   - It is enriched with database values (enabled state, custom min/max amounts, sort order)
   - This allows merchants to override API defaults while preserving the plan structure

### Key Behavior

- If API keys are obscured (not modified) during form submission, fee plans are retrieved from POST data via `fieldsToSaveFromPost()` instead of the API
- This prevents unnecessary API calls and preserves merchant-configured settings when only updating other configuration fields
