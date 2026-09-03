from bs4 import BeautifulSoup

html_path = r"C:\Users\LENOVO\Downloads\AZ-303-220-sequential-clean-final.html"

with open(html_path, 'r', encoding='utf-8') as f:
    content = f.read()

soup = BeautifulSoup(content, 'html.parser')
q11 = soup.find('article', id='q11')

if q11:
    print("--- Q11 HTML FROM MASTER FILE ---")
    print(q11.prettify()[:3000])
else:
    print("q11 not found in HTML!")
