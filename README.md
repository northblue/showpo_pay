This is a magento extension for processing orders from magento backend with showpo online payment method.

Installation
The extension should be upload into magento root folder and enabled from System->Configuration->Sales->Payment Methods->Showpo Online Payment.

How to use it
This extension will enable a payment method (Showpo Online Payment) which is available for backend orders only.

It will be triggered when admin is trying to generate the invoice for a pending order from magento backend. An API call will be made to pass order amount, customer id and quote id. The response will be status, amount, reference and txn_ref. The information for txn_ref will be save into additional information for the transaction.

Debug
A log named showpo_pay.log will record the input and response for all API calls.


Some thoughts about this extension
The endpoint url should be moved in to admin section if the url could be changed later.

Was trying to start the API call inside the validate() function and then trigger the capture() function after get response. Soif the request was rejected, the admin will still be able to select other method to process the order. But not sure why, the validate() function was always been called three times when admin trying to place the order. Wasted some time on it but still can not figure out why. I was ended up with starting the API call when admin submit the invoice.  Submit invoice will trigger the capture() and the API response will be saved into transaction. The reason I didn’t do that at the beginning is that the admin will not be able to use other payment method to invoice the order (The payment method cannot be changed after the order generated). The admin only can try it again later with the same payment method. Still think there must be a better to solve this issue. Will dig a bit deeper for the magento payment life cycle later

