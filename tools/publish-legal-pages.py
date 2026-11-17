"""Publishes the four policy pages in their structured form.

Each page opens with an "at a glance" summary, then numbered sections. Paragraphs are single
lines on purpose: WordPress turns hard line breaks inside a paragraph into <br> tags.
Shipping costs come from the [wr_shipping_rates] shortcode, so the policy always matches checkout.
"""
import base64
import json
import os
import urllib.error
import urllib.request

SITE = os.environ["WP_SITE_URL"].rstrip("/")
AUTH = base64.b64encode(
    f'{os.environ["WP_USER"]}:{os.environ["WP_APP_PASSWORD"]}'.encode()
).decode()

EMAIL = "wrightapparel@hotmail.com"
PHONE = "(762) 248-9684"
ADDRESS = "485 Brantley Cove CIR, Grovetown, GA 30813, United States"
WEB = "wrightparel.works"
ENTITY = "Wright Apparel is registered in the State of Georgia, United States."


def facts(*rows):
    items = "".join(f"<dt>{k}</dt><dd>{v}</dd>" for k, v in rows)
    return f'<aside class="wr-keyfacts"><p class="wr-keyfacts__title">At a glance</p><dl>{items}</dl></aside>'


def details_table():
    return (
        '<div class="wr-table-wrap"><table class="wr-size-table"><tbody>'
        f"<tr><th>Business address</th><td>{ADDRESS}</td></tr>"
        f'<tr><th>Email</th><td><a href="mailto:{EMAIL}">{EMAIL}</a></td></tr>'
        f'<tr><th>Phone</th><td><a href="tel:+17622489684">{PHONE}</a></td></tr>'
        f"<tr><th>Website</th><td>{WEB}</td></tr>"
        "<tr><th>Hours</th><td>Monday to Friday, 9am to 5pm Eastern Time</td></tr>"
        "</tbody></table></div>"
    )


def contact_section(lead):
    return f"<h2>Contact us</h2><p>{lead}</p>{details_table()}"


TERMS = (
    f"<p>These Terms &amp; Conditions govern your use of {WEB} and any purchase you make from it. By using the site or placing an order you agree to them. If you do not agree, please do not use the site.</p>"
    + facts(
        ("Seller", "Wright Apparel, Georgia, United States"),
        ("Currency", "US dollars (USD)"),
        ("How orders are fulfilled", "Made to order. Our partner suppliers make or pick each item and ship it to you."),
        ("Returns", "30 days from delivery. See the Refund &amp; Returns policy."),
        ("Governing law", "State of Georgia, United States"),
    )
    + "<h2>1. Who we are</h2>"
    f"<p>{ENTITY} In these terms, &ldquo;we&rdquo;, &ldquo;us&rdquo; and &ldquo;our&rdquo; mean Wright Apparel, and &ldquo;you&rdquo; means the person using the site or buying from it.</p>"
    + details_table()
    + "<h2>2. Eligibility</h2>"
    "<p>You must be at least 18 years old, or have the consent of a parent or guardian, to place an order. You are responsible for keeping your account password confidential and for activity that happens under your account.</p>"
    "<h2>3. Products, descriptions and availability</h2>"
    "<p>We describe every item as accurately as we can, including fabric, measurements and care instructions. Product photographs show the garment you will receive, but displays vary and colours may appear slightly different on screen.</p>"
    "<p><strong>How orders are fulfilled.</strong> We operate a made-to-order model: we do not hold stock ourselves. When you place an order we pass it to a partner supplier, who makes or picks the item and ships it directly to you. Our suppliers are selected by us and act on our instructions. Because availability is confirmed by the supplier after you order, an item can occasionally turn out to be unavailable. If that happens we will tell you promptly and refund that item in full.</p>"
    "<h2>4. Prices and payment</h2>"
    "<p>All prices are shown in US dollars and exclude any sales tax, which is calculated at checkout where applicable. We may change prices at any time, but the price you see when you place an order is the price that applies to that order. We accept the payment methods shown at checkout. Where you pay on delivery, payment is due at the time the order is handed over.</p>"
    "<p>If we discover a pricing error after you order, we will contact you before dispatch. You can then confirm the order at the correct price or cancel it for a full refund.</p>"
    "<h2>5. Orders</h2>"
    "<p>Your order is an offer to buy. It is accepted when we send you an order confirmation email. We may decline or cancel an order if an item is unavailable, if the price or description was clearly wrong, or if we suspect fraud. If we cancel an order you have paid for, we refund you in full.</p>"
    "<p>You are contracting with Wright Apparel, not with our suppliers. We remain responsible to you for the order, including delivery, faults and refunds, even though a supplier ships it.</p>"
    "<h2>6. Shipping and returns</h2>"
    '<p>Delivery times, rates and destinations are set out in our <a href="/shipping-policy/">Shipping Policy</a>. Returns, exchanges and refunds are set out in our <a href="/refund-and-returns/">Refund &amp; Returns</a> policy. Both form part of these terms.</p>'
    "<h2>7. Your information</h2>"
    '<p>We handle your personal information as described in our <a href="/privacy/">Privacy Policy</a>.</p>'
    "<h2>8. Intellectual property</h2>"
    "<p>All content on this site, including the Wright Parel name and logo, text, photographs and design, is owned by us or used with permission. You may not copy, reproduce or use it commercially without our written permission.</p>"
    "<h2>9. Acceptable use</h2>"
    "<p>You agree not to misuse the site: not to attempt to gain unauthorised access to it, not to introduce malware, not to scrape it in a way that affects other customers, and not to place orders in bad faith or to resell our products as your own brand.</p>"
    "<h2>10. Limitation of liability</h2>"
    "<p>To the fullest extent permitted by law, we are not liable for indirect or consequential losses, or for loss of profit, arising from your use of the site. Nothing in these terms limits liability that cannot lawfully be limited, including for death or personal injury caused by negligence, or your statutory rights as a consumer.</p>"
    "<h2>11. Governing law</h2>"
    "<p>These terms are governed by the laws of the State of Georgia, United States. Any dispute will be dealt with by the courts of Georgia, unless the law gives you the right to bring proceedings where you live.</p>"
    "<h2>12. Changes to these terms</h2>"
    "<p>We may update these terms from time to time. The version in force when you place an order is the one that applies to it. The date at the top of this page shows when they were last revised.</p>"
    + contact_section("Questions about these terms? Write to us or call.")
)

PRIVACY = (
    f"<p>This Privacy Policy explains what personal information Wright Apparel collects through {WEB}, why we collect it, who we share it with, and what choices you have. We keep it in plain language.</p>"
    + facts(
        ("Who is responsible", "Wright Apparel, Georgia, United States"),
        ("What we collect", "Your name, contact and delivery details, order history and messages to us"),
        ("Card details", "Never stored by us. Payments are handled by our payment providers."),
        ("Do we sell your data?", "No, never"),
        ("Who receives it", "Partner suppliers, payment providers and delivery carriers, only as needed to fulfil your order"),
        ("Your rights", f'Ask to see, correct or delete your data. Email <a href="mailto:{EMAIL}">{EMAIL}</a>.'),
    )
    + "<h2>1. Who is responsible for your information</h2>"
    f"<p>{ENTITY} We are the business that decides how your information is used.</p>"
    + details_table()
    + "<h2>2. What we collect</h2>"
    "<p><strong>Information you give us:</strong> your name, email address, phone number, shipping and billing address, the contents of messages you send us, and your account login details.</p>"
    "<p><strong>Order information:</strong> what you bought, order value, delivery status, and returns or exchanges.</p>"
    "<p><strong>Payment information:</strong> payments are processed by our payment providers. We receive confirmation that a payment succeeded and a transaction reference. We do not store your full card number on this site.</p>"
    "<p><strong>Technical information:</strong> IP address, browser and device type, and pages you visit, collected through cookies and similar technologies.</p>"
    "<h2>3. Why we use it</h2>"
    "<ul>"
    "<li>To take, process and deliver your order, and to handle returns.</li>"
    "<li>To send order and delivery updates.</li>"
    "<li>To answer your questions and provide customer service.</li>"
    "<li>To prevent fraud and keep the site secure.</li>"
    "<li>To understand how the site is used so we can improve it.</li>"
    "<li>To send marketing emails, only where you have asked for them. You can unsubscribe at any time using the link in the email.</li>"
    "<li>To meet our legal, tax and accounting obligations.</li>"
    "</ul>"
    "<h2>4. Cookies</h2>"
    "<p>Cookies are small files a site stores in your browser. We use them to keep your cart and session working, to remember your preferences, and to measure site traffic. You can delete or block cookies in your browser settings. The cart and checkout may not work properly if you block the essential ones.</p>"
    "<h2>5. Who we share it with</h2>"
    "<p>We do not sell your personal information. We share it only with the providers who help us run the store, and only as much as they need:</p>"
    "<ul>"
    "<li><strong>Partner suppliers</strong>, who make or pick your order and ship it to you. They receive your name, delivery address and order contents so they can fulfil it.</li>"
    "<li><strong>Payment providers</strong>, to process payment.</li>"
    "<li><strong>Shipping carriers and postal services</strong>, to deliver your order.</li>"
    "<li><strong>Our website host and email provider</strong>, to run the site and send messages.</li>"
    "<li><strong>Accountants and advisers</strong>, where required.</li>"
    "<li><strong>Authorities</strong>, where the law requires it.</li>"
    "</ul>"
    "<h2>6. How long we keep it</h2>"
    "<p>Order records are kept for as long as tax and accounting rules require, currently around seven years. Account information is kept while your account is open. Marketing preferences are kept until you unsubscribe. Messages you send us are kept for two years.</p>"
    "<h2>7. Your rights</h2>"
    f'<p>You can ask us to show you the personal information we hold about you, correct it, delete it, or stop using it for marketing. Write to <a href="mailto:{EMAIL}">{EMAIL}</a> and we will respond within 30 days.</p>'
    "<p>If you are in California, you also have the right to know what we collect and to ask us not to sell or share it; we do not sell it. If you are in the European Economic Area or the United Kingdom, you have rights under the GDPR, including the right to complain to your local supervisory authority.</p>"
    "<h2>8. Security</h2>"
    "<p>The site runs over an encrypted connection (HTTPS) and access to customer information is restricted to the people who need it to run the store. No system is perfectly secure, so we cannot promise absolute security, but we take reasonable steps to protect your information.</p>"
    "<h2>9. Children</h2>"
    "<p>Our store is for adults. We do not knowingly collect information from children under 13. If you believe a child has given us information, contact us and we will delete it.</p>"
    "<h2>10. Changes</h2>"
    "<p>If we change this policy we will update the date at the top of the page. If the change is significant we will make it clear on the site.</p>"
    + contact_section("Questions about your information? Write to us or call.")
)

SHIPPING = (
    f"<p>This Shipping Policy explains where we deliver, what it costs, and when your order will arrive. {ENTITY}</p>"
    + facts(
        ("We ship to", "Worldwide. Rates for your country are shown at checkout."),
        ("Order passed to supplier", "Within 1 business day"),
        ("Supplier dispatch", "2 to 5 business days"),
        ("Delivery", "3 to 7 business days after dispatch. Allow up to 14 business days from order to doorstep."),
        ("Shipping cost", "[wr_shipping_rates format=inline]"),
        ("Carriers", "USPS, UPS or FedEx"),
        ("Tracking", "Emailed for every parcel"),
    )
    + "<h2>1. How we fulfil orders</h2>"
    "<p>We do not hold stock. Each order is passed to one of our partner suppliers, who makes or picks the item and sends it directly to you. This keeps our range small and our prices honest, and it is why the packaging you receive may come from a fulfilment partner rather than from our address in Georgia.</p>"
    "<p>If your order contains items from more than one supplier, <strong>they will usually arrive in separate packages and may arrive on different days.</strong> You will get a tracking number for each parcel. Nothing extra is charged for a split delivery.</p>"
    "<h2>2. Shipping rates</h2>"
    "[wr_shipping_rates]"
    "<p>Rates are in US dollars and the rate that applies to your order is shown at checkout before you pay. When an order qualifies for free shipping, that is the option you are offered.</p>"
    "<h2>3. Where we ship</h2>"
    "<p>We ship worldwide. Shipping rates for your country are shown at checkout before you pay, and the rate that applies to your order is the one you are offered.</p>"
    "<p><strong>Customs duties and import taxes.</strong> Orders sent outside the United States may be subject to import duties, taxes and clearance fees charged by your country's customs authority. These are set by your government, are not included in your order total, and are your responsibility. Parcels held for unpaid charges may be delayed or returned to us.</p>"
    f'<p>Questions about delivery to your country? Write to <a href="mailto:{EMAIL}">{EMAIL}</a>.</p>'
    "<h2>4. Processing time</h2>"
    "<p>Orders are passed to our suppliers the same or next business day, Monday to Friday, excluding public holidays. Orders placed at the weekend are processed from Monday. Suppliers dispatch within 2 to 5 business days, and made-to-order items can take longer. Where we know a piece takes extra time, the product page says so.</p>"
    "<h2>5. Delivery time</h2>"
    "<p>Standard delivery within the United States takes 3 to 7 business days after dispatch, and up to 14 business days in total from the order date. International orders typically take 7 to 21 business days after dispatch, depending on the destination and customs clearance. Remote areas can take longer. Once each parcel leaves the supplier you will receive its tracking number by email.</p>"
    "<h2>6. Carriers</h2>"
    "<p>Parcels travel with national carriers: USPS, UPS or FedEx, depending on the supplier and the size and weight of the parcel. We do not charge extra for the carrier used. The carrier name and tracking number are shown in your dispatch email, so you always know who has your order.</p>"
    "<h2>7. Tracking</h2>"
    "<p>You will receive a dispatch email with a tracking link for each parcel. If tracking has not updated for more than 5 business days, contact us with your order number.</p>"
    "<h2>8. Lost, delayed or damaged parcels</h2>"
    "<p>If a parcel arrives damaged, keep the packaging and contact us within 7 days with photographs, and we will replace the item or refund you. If a parcel is lost in transit, let us know and we will open a claim with the carrier and either resend or refund your order.</p>"
    "<h2>9. Addresses</h2>"
    "<p>Please check your address before completing checkout. We cannot redirect a parcel once it has been dispatched. If an order is returned to us because the address was wrong or nobody could receive it, we will contact you to arrange redelivery, and the cost of the second delivery is your responsibility.</p>"
    "<h2>10. Changing or cancelling an order</h2>"
    "<p>Contact us as soon as you can and we will do our best. Once a supplier has dispatched an item we can no longer cancel it, but you can still return it under our Refund &amp; Returns policy.</p>"
    + contact_section("Questions about a delivery? Write to us or call, and include your order number.")
)

REFUNDS = (
    f"<p>We want you to be able to order with confidence. This policy explains how returns, exchanges and refunds work. {ENTITY}</p>"
    + facts(
        ("Return window", "30 days from the day your parcel is delivered"),
        ("Condition", "Unworn, unwashed, with any tags attached"),
        ("Return shipping", "Free within the United States. Outside the US, paid by you."),
        ("Refund timing", "Within 5 to 10 business days of us receiving your return"),
        ("Refunded to", "Your original payment method"),
        ("To start a return", f'Email <a href="mailto:{EMAIL}">{EMAIL}</a> with your order number'),
    )
    + "<h2>1. 30-day returns</h2>"
    "<p>You may return any item within 30 days of delivery for a refund or exchange. Returns are free within the United States: we will send you a prepaid label. For orders shipped outside the United States, return postage is paid by you. The 30 days run from the day the parcel is delivered.</p>"
    "<h2>2. Condition of returned items</h2>"
    "<p>Items must come back unworn, unwashed and free of odours, with any tags still attached and in the original packaging where possible. Please try garments on over your own clothing. We may refuse a refund, or offer a reduced one, if an item comes back clearly worn or damaged in a way that is not a manufacturing fault.</p>"
    "<h2>3. How to start a return</h2>"
    f'<p>Email <a href="mailto:{EMAIL}">{EMAIL}</a> with your order number and the item you want to return, and say whether you would like a refund or an exchange. We will reply within two business days with a prepaid label and instructions. Returns are arranged by us, not by the supplier, so please do not send items back to the supplier address on the parcel.</p>'
    "<p>Please send returns to:</p>"
    "<p>Wright Apparel<br>485 Brantley Cove CIR<br>Grovetown, GA 30813<br>United States</p>"
    "<h2>4. Refund timing</h2>"
    "<p>Once your return arrives we inspect it within 3 business days. Approved refunds are issued to the original payment method within 5 to 10 business days, depending on your bank. If you paid on delivery, we will agree a refund method with you by email. We email you when the refund is issued. Original shipping charges are not refunded unless the item was faulty or we sent the wrong thing.</p>"
    "<h2>5. Exchanges</h2>"
    "<p>If you need a different size, tell us in your email and we will reserve it if it is in stock and send it once your return is on its way. If the size you want is unavailable we will refund you instead.</p>"
    "<h2>6. Items that cannot be returned</h2>"
    "<p>For hygiene reasons, opened socks cannot be returned unless they are faulty. Gift cards and items marked final sale cannot be returned. This does not affect your rights if an item is faulty or not as described.</p>"
    "<h2>7. Faulty or incorrect items</h2>"
    "<p>If something arrives faulty, or is not what you ordered, contact us within 30 days with your order number and a photograph. We will cover the return postage and either replace the item or refund you in full, including the original delivery cost. Faults caused by normal wear, accident or washing against the care label are not covered.</p>"
    "<h2>8. Late or missing refunds</h2>"
    f'<p>If you have not received a refund within 10 business days of us confirming it, check with your bank first, as it can take a few days to appear. If it is still missing, write to <a href="mailto:{EMAIL}">{EMAIL}</a> and we will trace it.</p>'
    + contact_section("Questions about a return? Write to us or call, and include your order number.")
)

PAGES = {
    19: ("Terms & Conditions", TERMS),
    243: ("Privacy Policy", PRIVACY),
    21: ("Shipping Policy", SHIPPING),
    22: ("Refund & Returns", REFUNDS),
}


SLUGS = {19: "terms-and-conditions", 243: "privacy", 21: "shipping-policy", 22: "refund-and-returns"}


def main():
    out_dir = os.environ.get("OUT_DIR")
    if out_dir:
        os.makedirs(out_dir, exist_ok=True)
        for page_id, (title, content) in PAGES.items():
            with open(os.path.join(out_dir, SLUGS[page_id] + ".html"), "w") as handle:
                handle.write(content)
            print(f"wrote {SLUGS[page_id]}.html ({len(content)} bytes)")
        return
    problems = []
    for page_id, (title, content) in PAGES.items():
        payload = json.dumps({"title": title, "content": content, "status": "publish"}).encode()
        req = urllib.request.Request(f"{SITE}/wp-json/wp/v2/pages/{page_id}", method="POST", data=payload)
        req.add_header("Authorization", "Basic " + AUTH)
        req.add_header("Content-Type", "application/json")
        try:
            with urllib.request.urlopen(req, timeout=90) as response:
                body = json.loads(response.read())
            saved = body["content"]["raw"] if "raw" in body.get("content", {}) else ""
            print(f'OK   {page_id:<4} {title:<20} {len(content):>6} bytes  link={body.get("link")}')
        except urllib.error.HTTPError as exc:
            detail = json.loads(exc.read() or b"{}")
            problems.append(f"{page_id} {title}: [{exc.code}] {detail.get('message')}")
            print("FAIL", page_id, title, exc.code, detail.get("message"))
    print("\nproblems:", len(problems))
    for row in problems:
        print(" -", row)


main()
