import json
import time
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from bs4 import BeautifulSoup

def unescape_title(title):
    return bytes(title, "utf-8").decode("unicode_escape")

# Step 1: Read and parse the output.json file
with open('output.json', 'r', encoding='utf-8') as file:
    data = json.load(file)

# Open browser
driver = webdriver.Chrome()  # or webdriver.Firefox()

# Step 2: Scrape the book information from the Goodreads website
url = 'https://www.goodreads.com/review/list/44073323-jonatan-jansson?shelf=%23ALL%23'
driver.get(url)

# Close the popup
try:
    wait = WebDriverWait(driver, 10)
    close_popup = wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, ".modal__close")))
    close_popup.click()
except:
    print("Could not close popup")

# Try scrolling 3 times
for _ in range(3):
    # Scroll down
    driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
    time.sleep(10)

    # Parse the page
    soup = BeautifulSoup(driver.page_source, 'html.parser')

    # Create a dictionary with book title and cover URL
    book_covers = {}
    for title_td, cover_td in zip(soup.select("td.field.title"), soup.select("td.field.cover")):
        title = title_td.select_one("div.value a").text.strip()
        cover_url = cover_td.select_one("div.value a img")["src"]
        book_covers[title] = cover_url

    # Step 3, 4: Update the JSON data
    found_all = True
    for category in data:
        for book in data[category]:
            title = unescape_title(book["Title"])
            if title in book_covers:
                book["Coverart"] = book_covers[title]
            else:
                found_all = False

    if found_all:
        break

else:
    print("Could not find all titles after 3 scrolls")

# Close the browser
driver.quit()

# Step 5: Write the modified JSON data back to the file
with open('output.json', 'w', encoding='utf-8') as file:
    json.dump(data, file, indent=4, ensure_ascii=False)
