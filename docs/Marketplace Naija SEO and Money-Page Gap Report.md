# **Marketplace Naija SEO and Money-Page Gap Report**

**Competitor:** [Jiji.ng](http://Jiji.ng)  
**Focus:** Technical SEO, sitemap coverage, missing money pages, local SEO and GEO visibility

## **1\. Executive Summary**

Marketplace Naija already has:

* Category pages  
* State-based pages  
* Location-based brand pages  
* Seller profiles  
* Individual advert pages  
* Buy Direct functionality  
* Blog content

The main SEO issue is not simply that commercial pages are missing. Some valuable pages already exist but are not included in the XML sitemap.

Examples include:

* `/lagos/apple`  
* `/lagos/samsung`

These pages target valuable commercial searches but may not be discovered or recrawled as efficiently if they are excluded from the sitemap.

Jiji performs better because it has a deeper landing-page structure covering:

* Location \+ category  
* Location \+ brand  
* Location \+ model  
* Location \+ model \+ year  
* Property type \+ location  
* Bedroom count \+ location  
* Condition and price-based searches

Examples include:

* Toyota Corolla for sale in Lagos  
* iPhone 13 for sale in Ikeja  
* Two-bedroom apartments for rent in Lekki  
* Foreign-used cars in Abuja

Marketplace Naija should strengthen its existing page structure, add qualified pages to the sitemap and create deeper model and attribute pages based on inventory and search demand.

## **2\. Main Money-Page Opportunities**

### **A. City and LGA Pages**

Marketplace Naija already has state-level pages, but it needs stronger coverage for cities, LGAs and major commercial districts.

Priority examples:

* Cars for sale in Ikeja  
* Phones for sale in Ikeja  
* Apartments for rent in Lekki  
* Land for sale in Ibeju-Lekki  
* Cars for sale in Abuja  
* Cars for sale in Port Harcourt  
* Phones for sale in Ibadan

Recommended URL format:

/ikeja/mobile-phones

/lekki/houses-apartments-for-rent

/abuja/cars

These pages should only be indexed when they contain sufficient active inventory.

### **B. Existing Brand Pages Missing from the Sitemap**

Marketplace Naija already has location-based brand pages such as:

/lagos/apple

/lagos/samsung

The issue is that these commercially valuable pages are missing from the XML sitemap.

They should be added to a dedicated sitemap if they:

* Return a `200 OK` status  
* Are indexable  
* Have self-referencing canonical tags  
* Contain sufficient active inventory  
* Use unique titles and descriptions  
* Are internally linked from relevant category pages

A dedicated sitemap could be created:

/sitemaps/location-brand-pages.xml

This sitemap may include qualified pages such as:

/lagos/apple

/lagos/samsung

/abuja/apple

/abuja/samsung

/lagos/toyota

/lagos/lexus

### **C. Category-Specific Brand Pages**

Although location-brand pages already exist, URLs such as `/lagos/apple` may be too broad because Apple products can include phones, laptops, tablets and smartwatches.

A clearer structure would be:

/lagos/mobile-phones/apple

/lagos/mobile-phones/samsung

/lagos/cars/toyota

/lagos/cars/lexus

/lagos/laptops-computers/hp

This creates stronger category context for users and search engines.

Existing broad brand pages should not be removed immediately. Their traffic, indexation and backlinks should first be reviewed before redirects or canonical changes are implemented.

### **D. Model Pages**

Model pages target users who are closer to buying.

Priority examples:

/lagos/mobile-phones/apple-iphone-13

/lagos/mobile-phones/apple-iphone-14

/lagos/cars/toyota-corolla

/lagos/cars/toyota-camry

/lagos/cars/lexus-rx-350

These pages should aggregate all relevant active adverts instead of relying only on individual listing pages.

### **E. Attribute Pages**

Selected filters should become permanent landing pages when they have clear search demand.

Examples:

* Foreign-used cars in Lagos  
* Cars under ₦10 million in Lagos  
* UK-used iPhones in Ikeja  
* Two-bedroom apartments in Lekki  
* Furnished apartments in Abuja  
* Land for sale in Ibeju-Lekki

Marketplace Naija should not index every possible filter combination. Pages should only be created when they have:

* Sufficient inventory  
* Clear commercial intent  
* Unique content  
* Stable search demand

## **3\. Key Technical SEO Issues**

### **A. Sitemap Coverage**

Important location-based brand pages exist but are not included in the XML sitemap.

This can result in:

* Slower page discovery  
* Less frequent crawling  
* Incomplete sitemap reporting in Google Search Console  
* Reduced visibility for commercially important pages  
* Greater dependence on internal links

Marketplace Naija should create separate sitemap files for:

/sitemaps/categories.xml

/sitemaps/location-pages.xml

/sitemaps/location-brand-pages.xml

/sitemaps/models.xml

/sitemaps/listings.xml

/sitemaps/blog.xml

Only canonical, live and indexable pages should be included.

### **B. URL Inconsistency**

Marketplace Naija currently uses both uppercase and lowercase URLs.

Examples:

/Lagos/mobile-phones

/lagos/apple

Use lowercase URLs consistently:

/lagos/mobile-phones

/lagos/apple

All uppercase variations should be redirected to the lowercase versions.

### **C. Internal Search Pages**

Pages such as `/search` should normally use:

\<meta name="robots" content="noindex,follow"\>

Internal search pages should not be included in the sitemap.

### **D. Related Listing Pages**

Pages such as:

/related/33927

can create duplicate and low-value URLs.

They should normally be:

* Noindexed  
* Removed from the sitemap  
* Canonicalised to the closest category, brand or model page where appropriate

### **E. Empty Category Pages**

Pages with no active listings should not normally be indexed.

Suggested rule:

* 0 listings: noindex  
* 1–4 listings: usually noindex  
* 5–9 listings: review manually  
* 10+ listings: normally indexable

Inventory thresholds should vary by category.

### **F. Generic Metadata**

Some category and seller pages use general titles instead of page-specific metadata.

**Old title:**

Post Free Ads in Nigeria | Marketplace Naija

**Improved title:**

Houses and Apartments for Rent in Lagos | Marketplace Naija

Each page should have a title, H1 and description generated from its actual category, location, brand or model.

## **4\. Recommended Page Structure**

Use this hierarchy:

/{location}/{category}

/{location}/{category}/{brand}

/{location}/{category}/{model}

/{location}/{category}/{model-year}

/{location}/{category}/{attribute}

Examples:

/lagos/mobile-phones

/lagos/mobile-phones/apple

/lagos/mobile-phones/apple-iphone-13

/lagos/cars/toyota-corolla

/lagos/cars/toyota-corolla-2020

Avoid unclear URLs such as:

/apple-2

/category-3

Where existing broad URLs already receive traffic, proper redirects or canonical tags should be implemented before changing the structure.

## **5\. Content Required on Money Pages**

Each important landing page should contain more than a listing grid.

Include:

* Clear H1  
* Short introduction  
* Current listing count  
* Relevant price range  
* Popular brands or models  
* Buying tips  
* Safety information  
* Frequently asked questions  
* Related locations  
* Related categories

### **Example**

**H1:** Apple iPhones for Sale in Ikeja

**Introduction:**  
Browse new, UK-used and Nigerian-used Apple iPhones for sale in Ikeja. Compare models, prices, storage options and verified sellers on Marketplace Naija.

## **6\. Priority Pages to Create or Add to the Sitemap**

### **Mobile Phones**

Existing pages to review and add to the sitemap:

* Apple phones in Lagos  
* Samsung phones in Lagos  
* Apple phones in Abuja  
* Samsung phones in Abuja

New pages to create where inventory supports them:

* Apple phones in Ikeja  
* iPhone 11 in Lagos  
* iPhone 12 in Lagos  
* iPhone 13 in Lagos  
* iPhone 14 in Lagos  
* UK-used iPhones in Ikeja

### **Vehicles**

* Toyota cars in Lagos  
* Lexus cars in Lagos  
* Toyota Corolla in Lagos  
* Toyota Camry in Lagos  
* Toyota Highlander in Lagos  
* Foreign-used cars in Lagos  
* Cars under ₦10 million in Lagos

### **Real Estate**

* Apartments for rent in Ikeja  
* Apartments for rent in Lekki  
* Apartments for rent in Abuja  
* Two-bedroom apartments in Lekki  
* Furnished apartments in Lekki  
* Land for sale in Ibeju-Lekki  
* Houses for sale in Lekki

## **7\. Implementation Priorities**

### **Phase 1: Technical Fixes**

* Repair and validate sitemap delivery  
* Identify existing commercial pages missing from the sitemap  
* Add qualified location-brand pages to a dedicated sitemap  
* Standardise lowercase URLs  
* Noindex internal search pages  
* Noindex related-listing pages  
* Remove empty pages from the sitemap  
* Fix page titles and descriptions  
* Improve category accuracy  
* Standardise location names

### **Phase 2: Existing Page Optimisation**

Improve pages that already exist by adding:

* Unique metadata  
* Clear H1 headings  
* Introductory content  
* Internal links  
* Canonical tags  
* Breadcrumbs  
* Structured data  
* Relevant sitemap inclusion

### **Phase 3: New Money Pages**

Prioritise:

* Lagos  
* Abuja  
* Port Harcourt  
* Ibadan  
* Ogun

Start with:

* Cars  
* Mobile phones  
* Real estate

### **Phase 4: Deeper Pages**

Add:

* Category-specific brand pages  
* Model pages  
* Vehicle-year pages  
* Bedroom pages  
* Condition pages  
* Price-range pages

## **8\. Final Recommendation**

Marketplace Naija does not need thousands of random filter pages.

It needs a controlled SEO structure based on:

Location → Category → Brand → Model → Selected attributes

The immediate priority is to distinguish between:

1. Pages that already exist but are missing from the sitemap  
2. Existing pages that need stronger optimisation  
3. Pages that still need to be created

The biggest opportunities are:

* Better sitemap coverage  
* City and LGA pages  
* Category-specific brand pages  
* Model pages  
* Stronger metadata  
* Removal of low-value indexed URLs  
* More useful content on category pages  
* Improved GEO and AI-search visibility

Cars, Mobile Phones and Real Estate should remain the first categories to prioritise because they have strong commercial demand, rich product attributes and clear location-based search behaviour.

