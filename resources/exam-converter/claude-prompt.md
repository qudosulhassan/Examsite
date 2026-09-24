I've attached a practice-question file (PDF or Word) that I wrote myself or have a license to publish, and exam-template.html. Convert the exam into an online practice test by filling in the EXAM DATA block of exam-template.html, and give me the finished HTML file to download.

Exam code: {EXAM_CODE}
Exam name: {EXAM_NAME}
Vendor: {VENDOR}

How to fill the template
- Read the comment at the top of the EXAM DATA block in exam-template.html; it defines every field. Fill only EXAM_META, QUESTIONS, ANSWERS_B64, QIMG and AIMG. Keep all the engine code and styling exactly as it is.
- Set EXAM_META to {"code": "{EXAM_CODE}", "title": "{EXAM_NAME}"}, and keep the vendor name wherever the source mentions it.
- Build the file with code (read the source, extract the images, write the JSON, base64-encode ANSWERS, fill the template) rather than typing the data by hand, so nothing is cut off.
- Include every question in the source, in the source order, numbered as in the source. Copy question text, options, explanations and reference links word for word. Do not invent, summarise or "improve" content, and do not guess answers: if the source gives no answer, set it to null / [].
- Re-join words and sentences that were broken across lines or pages, skip bare page numbers, and fix broken characters (â€™, �, etc.). Keep any author, copyright or source notices that belong to the content.
- Types:
  - One correct option → "multiple-choice".
  - Two or more correct options, or "Choose two/three" → "multiple-select".
  - Yes/No statements, drop-down answer areas → "hotspot".
  - Drag items to targets, put steps in order → "drag-drop".
- Images: crop every exhibit, answer-area and answer image from the source and embed it as a JPEG data URI (quality around 80, long side at most 1400 px, readable text). Question exhibits go in QIMG and are listed in the question's "images"; answer images go in AIMG and are listed in "answerImages"; an image inside an explanation goes in the explanation as {"img": key}.
- Hotspot and drag & drop: whenever the statements / boxes / draggable items can be read from the source (text or image), build an interactive "ia" answer area (yesno, select, match or order) and put the correct answer for every row in "rowAnswers", read from the source's answer (text or answer image). The row labels and choices must be the real text from the source. Still include the answer-area image in "images" and the answer image in "answerImages". Only if the answer area really cannot be written as rows, leave "ia": null and rely on the images.
- Store the answers as ANSWERS_B64: base64 of the UTF-8 JSON object keyed by question id, exactly as the template describes.

Before you give me the file, check every question and fix any problem:
1. Every correctAnswer letter exists in that question's options, and multiple-choice has exactly one correct answer.
2. "Choose two/three" questions are multiple-select with that many answers.
3. Every "ia" has one rowAnswer per row, and each rowAnswer exactly matches one of that row's choices (or a pool item; Yes/No for yesno).
4. Every image key used in QUESTIONS or ANSWERS exists in QIMG / AIMG, and no image is blank or cut off.
5. Question ids are unique and in order, and no question from the source is missing.
6. Open the finished HTML and click through a few questions of each type to confirm Check Answer and Reveal Answer work.

Name the file {EXAM_CODE}-practice-test.html. If the exam has more than about 50 questions, split it into parts of about 50 questions ({EXAM_CODE}-part1.html, {EXAM_CODE}-part2.html, …) and give me one part per reply, so no part is cut off. Each part should be a complete file made from the same template, with question ids continuing from the previous part. When done, tell me how many questions of each type the file contains, and list any question where the source had no answer or an unreadable image.
