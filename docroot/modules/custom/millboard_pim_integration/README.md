Millboard PIM integration Modules.

Configure the Widen APIs settings
------------------------------------
1. Navigate to /admin/config/services/widen_api  it will display the Configure the widen API settings Form.
2. Adde Widen API URL(https://api.widencollective.com/v2) and  Bearer Token
3. Click on Save configuration Button.

Dush Commands(Run the commands in sequence as given below)
-------------------------------------------------------------

Product Types
---------------
1. Import product types texonomy run `ddev drush pim-cpt`
2. Navigate to `/admin/structure/taxonomy/manage/product_type/overview`
3. It will display a list of imported product types.

Product Category
------------------
1. Import product category texonomy run `ddev drush pim-cpc`
2. Navigate to `/admin/structure/taxonomy/manage/product_category/overview`
3. It will display a list of imported product categories.


Products
---------
1. Import products run `ddev drush pim-cp`
3. Navigate to `/admin/commerce/products`
4. It will display a list of imported products.

Product Variations
------------------
1. Import product variations run `ddev drush pim-cpv`
