import re
import json
from bs4 import BeautifulSoup

html_path = r"C:\Users\LENOVO\Downloads\AZ-303-220-sequential-clean-final.html"

with open(html_path, 'r', encoding='utf-8') as f:
    content = f.read()

soup = BeautifulSoup(content, 'html.parser')
cards = soup.find_all('article', class_='question-card')

print(f"Found {len(cards)} question cards.")

parsed_questions = []

for card in cards:
    q_num = card.get('data-number', '')
    
    type_badge = card.find('span', class_='type-badge')
    q_type_str = type_badge.get_text(strip=True) if type_badge else 'Multiple Choice'
    
    # Determine question type
    if 'Hotspot' in q_type_str:
        q_type = 'hotspot'
    elif 'Drag' in q_type_str:
        q_type = 'drag_drop'
    elif 'Multiple Answer' in q_type_str:
        q_type = 'multiple_choice'
    else:
        q_type = 'single_choice'
        
    q_content_div = card.find('div', class_='question-content')
    
    # Extract paragraphs inside q_content_div (excluding options)
    ps = []
    if q_content_div:
        for child in q_content_div.children:
            if child.name == 'p':
                ps.append(child.decode_contents().strip())
            elif child.name == 'div' and 'options' in child.get('class', []):
                break
                
    q_text = "<br>".join(ps) if ps else (q_content_div.get_text(strip=True) if q_content_div else '')
    
    # Options
    options = []
    options_div = card.find('div', class_='options')
    if options_div:
        labels = options_div.find_all('label', class_='option')
        for label in labels:
            inp = label.find('input')
            val = inp.get('value', '').strip() if inp else ''
            text_span = label.find('span', class_='option-text')
            text = text_span.get_text(strip=True) if text_span else label.get_text(strip=True)
            if val and text:
                options.append({'key': val, 'text': text})
                
    # Correct Answer
    ans_val_div = card.find('div', class_='answer-value')
    correct_ans = ans_val_div.get_text(strip=True) if ans_val_div else ''
    
    # Explanation
    exp_div = card.find('div', class_='explanation')
    explanation = exp_div.decode_contents().strip() if exp_div else ''
    
    # Exhibits / Images
    exhibits = []
    exhibits_div = card.find('div', class_='exhibits')
    if exhibits_div:
        imgs = exhibits_div.find_all('img')
        for img in imgs:
            src = img.get('src', '')
            if src:
                exhibits.append(src)
                
    parsed_questions.append({
        'question_number': q_num,
        'question_type': q_type,
        'question_text': q_text,
        'options': options,
        'correct_answer': correct_ans,
        'explanation': explanation,
        'exhibits': exhibits
    })

print(f"Sample Question 1: {json.dumps(parsed_questions[0], indent=2)}")
print(f"Sample Question 4 (Hotspot): {json.dumps(parsed_questions[3], indent=2)}")

# Save to JSON file
with open(r"C:\ExamSite\examsninja\scratch\az303_parsed.json", 'w', encoding='utf-8') as f:
    json.dump(parsed_questions, f, indent=2)

print("Saved to scratch/az303_parsed.json")
