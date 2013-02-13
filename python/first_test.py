from selenium import webdriver
from selenium.common.exceptions import NoSuchElementException
from selenium.webdriver.common.keys import Keys
import time

# Get browser session
browser = webdriver.Firefox()

# Set implicit wait
browser.implicitly_wait(10)

# Load CW home page
browser.get("http://www.climbingweather.com")

# Assert ClimbingWeather.com in title
assert "ClimbingWeather.com" in browser.title

# Find the search box
search = browser.find_element_by_id("globalSearch")
search.send_keys("Little Cottonwood Canyon" + Keys.RETURN)

# Try to fink link
try:
    area_link = browser.find_element_by_partial_link_text("Little Cottonwood Canyon")
except NoSuchElementException:
    assert 0, "Little Cottonwood Canyon was not in search results"

# Click area link
area_link.click()

# Verify area has 7 forecast days
forecast_days = len(browser.find_elements_by_xpath("//table[contains(@class, 'forecast')]//td[contains(@class, 'day_date')]"))
assert forecast_days == 7, "Expected 7 forecast days but got {0}".format(forecast_days)

# Close browser
browser.close()
