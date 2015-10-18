TED-talks-details-from-TED.com-and-youtube
==========================================
#### [you can use this public api](http://market.mashape.com/bestapi/ted/): <a href="https://www.mashape.com/bestapi/ted?&amp;utm_campaign=mashape5-embed&amp;utm_medium=button&amp;utm_source=ted&amp;utm_content=anchorlink&amp;utm_term=icon-light"><img src="https://d1g84eaw0qjo7s.cloudfront.net/images/badges/badge-icon-light-9e8eba63.png" width="143" height="38" alt="Mashape"></a>

this is an example of you to get talks data

this is a PHP script that should run from command line / as a crone job

main objective of this is to get details of TED talk from the offical ted web site and from
YouTube

this will go over all video pages on http://www.ted.com/talks/view/id/
from id=1 till the end when there are no more ID's to query
on each page will take some relevant data and store it

afterward for each TED talk saved from ted.com a query will be sent via YouTube api to get more details that
is missing in the ted.com web site

in the end there will be a full list of all TED talks with data from ted.com (like title, tags etc..) 
and data from youtube.com (like view count, video src etc....)
