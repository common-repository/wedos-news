/** WEDOS News plugin
 *  ----------------------------------------------------------------------------
 *  Version: 0.2
 */

jQuery(document).ready(function($) {
$("#wedos-widget").fadeIn(600);
$("#wedos-google-plus-link").click(function(){
$("#wedos-rss-datacentrum").hide(500);
$("#wedos-traffic").hide(500);
$("#wedos-google-plus").show(500);
$(this).removeClass("inactive");
$(this).addClass("active");
$("#wedos-datacentrum-link").removeClass("active");
$("#wedos-datacentrum-link").addClass("inactive");
$("#wedos-traffic-link").removeClass("active");
$("#wedos-traffic-link").addClass("inactive");
});
$("#wedos-datacentrum-link").click(function(){
$("#wedos-google-plus").hide(500);
$("#wedos-traffic").hide(500);
$("#wedos-rss-datacentrum").show(500);
$(this).removeClass("inactive");
$(this).addClass("active");
$("#wedos-google-plus-link").removeClass("active");
$("#wedos-google-plus-link").addClass("inactive");
$("#wedos-traffic-link").removeClass("active");
$("#wedos-traffic-link").addClass("inactive");
});
$("#wedos-traffic-link").click(function(){
$("#wedos-google-plus").hide(500);
$("#wedos-rss-datacentrum").hide(500);
$("#wedos-traffic").show(500);
$(this).removeClass("inactive");
$(this).addClass("active");
$("#wedos-google-plus-link").removeClass("active");
$("#wedos-google-plus-link").addClass("inactive");
$("#wedos-datacentrum-link").removeClass("active");
$("#wedos-datacentrum-link").addClass("inactive");
});
$("a#wedos-help-link").click(function () {
window.location = 'http://kb.wedos.com/';
event.preventDefault();
});
$("a#wedos-forum-link").click(function () {
window.location = 'http://kb.wedos.com/forum/index.html';
event.preventDefault();
});
$("a#wedos-admin-link").click(function () {
window.location = 'https://client.wedos.com/';
event.preventDefault();
});
$("a#wedos-domena").click(function () {
window.location = 'https://hosting.wedos.com/cs/domeny/order.html?tld=cz&step=1';
event.preventDefault();
});
$("a#wedos-hosting").click(function () {
window.location = 'https://hosting.wedos.com/cs/webhosting/order.html?step=1';
event.preventDefault();
});
$("a#wedos-disk").click(function () {
window.location = 'https://disk.wedos.com/cs/order.html?variant_id=30&step=1';
event.preventDefault();
});
$("a#wedos-traffic-link-info").click(function () {
window.location = 'http://datacentrum.wedos.com/traffic.html';
event.preventDefault();
});
});
function windowopen(URL,WIDTH,HEIGHT){
if (!WIDTH) WIDTH = 780;
if (!HEIGHT) HEIGHT = 600;
window.open(URL,'_blank','directories=0,location=0,menubar=0,resizable=1,scrollbars=1,status=0,toolbar=0,height='+(HEIGHT+15)+',width='+WIDTH+',top='+((screen.availHeight/2)-(HEIGHT/2))+',left='+((screen.availWidth/2)-(WIDTH/2)));}
