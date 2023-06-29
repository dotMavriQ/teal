import json
from collections import namedtuple

Book = namedtuple('Book', ['title', 'pages', 'exclusive_shelf'])

def extract_books(json_content):
    """Extracts book titles, page counts, and exclusive shelf types from JSON content."""
    books = []
    data = json.loads(json_content)
    for shelf_type in ['read', 'to-read']:
        for entry in data[shelf_type]:
            title = entry['Title']
            pages_str = entry['Number of Pages']
            if pages_str:
                pages = int(pages_str)
                books.append(Book(title, pages, shelf_type))
    return books

def filter_books(books):
    """Filters books and includes only those marked as 'to-read'."""
    return [book for book in books if book.exclusive_shelf == 'to-read']

def get_top_10_books(books):
    """Returns the top 10 books with the lowest number of pages."""
    return sorted(books, key=lambda x: x.pages)[:50]

# Read the output.json file
with open('output.json', 'r') as file:
    json_content = file.read()

# Extract books from the JSON content
books = extract_books(json_content)

# Filter books based on the exclusive shelf type
filtered_books = filter_books(books)

# Get the top 10 books with the lowest number of pages
top_10_books = get_top_10_books(filtered_books)

# Display the top 10 books
for book in top_10_books:
    print(f"Title: {book.title}\tPages: {book.pages}")
