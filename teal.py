import csv
import json
import locale
from collections import defaultdict

# Set the locale to Swedish
locale.setlocale(locale.LC_ALL, 'sv_SE.utf8')

# Read the CSV file
csv_file = 'goodreads_library_export.csv'
json_file = 'output.json'

# Read CSV content
data = defaultdict(list)
with open(csv_file, mode='r', encoding='utf-8') as file:
    csv_reader = csv.DictReader(file)
    for row in csv_reader:
        data[row['Exclusive Shelf']].append(row)

# Custom sort function for Swedish locales
def custom_sort(item):
    return locale.strxfrm(item['Title'])

# Sort the data within each 'Exclusive Shelf' group by 'Title'
for exclusive_shelf in data:
    data[exclusive_shelf].sort(key=custom_sort)

# Write data to JSON file
with open(json_file, 'w', encoding='utf-8') as file:
    json.dump(data, file, ensure_ascii=False, indent=4)
