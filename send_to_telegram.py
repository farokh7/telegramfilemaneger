from telethon.sync import TelegramClient
from telethon.sessions import StringSession
import sys
import os
import json

# اطلاعات حساب
api_id = 26530688
api_hash = '9c5bb5d64b62bdcbd9f66a44ba854ba1'
string_session = '1BJWap1sBu0CS7q0dFAzAEpMEfCCqqqKQZiMQZbl75dka4Q4Q_r1Y4poaHgJo26Ud0o1GW95PlOguhOlpot0VbDzmWY6443YpMdLHuJvjJEsnPiIChI5S1qp_psSKtRSXW9TBUOHKsH9bq-F2tkh65TiR6cYkcA9_SuDyRlq3qMIqR-jbiT41KCXa8Zya02dX-Vjcm0I6ruRGXkTjZw8Md60LQKF_RXyyEc_u6mrra54ux0hp6ToHmD7jZM8tzaiEHAB7BJubZ2uEZbqk9sYg1-oenvskoE9YllxSuYb21NLBM3PDSASaoJiywgwUCDJnmrT6Rdn7DoTIAs6Aynk5RleXM9Xukpk='
channel = '@farokhfilems'
result_file = "telegram_result.json"

# حذف خروجی قبلی
if os.path.exists(result_file):
    os.remove(result_file)

# بررسی فایل ورودی
if len(sys.argv) < 2:
    print("Usage: python send_to_telegram.py <path-to-file>")
    sys.exit(1)

file_path = sys.argv[1]
if not os.path.exists(file_path):
    print(f"File does not exist: {file_path}")
    sys.exit(1)

try:
    with TelegramClient(StringSession(string_session), api_id, api_hash) as client:
        message = client.send_file(channel, file_path, caption="File uploaded by Telegram File Manager")

        file_id = None

        if hasattr(message, 'media') and hasattr(message.media, 'document') and hasattr(message.media.document, 'id'):
            file_id = message.media.document.id
        elif hasattr(message, 'photo') and hasattr(message.photo, 'id'):
            file_id = message.photo.id

        if file_id:
            message_link = f"https://t.me/{channel.strip('@')}/{message.id}"
            with open(result_file, "w", encoding="utf-8") as f:
                json.dump({
                    "telegram_file_id": file_id,
                    "telegram_url": message_link
                }, f)
            print("File sent and link saved.")
        else:
            raise Exception("message.media.document.id and message.photo.id not found. Cannot extract file ID.")

except Exception as e:
    safe_error = str(e).encode("ascii", errors="ignore").decode("ascii")
    with open(result_file, "w", encoding="utf-8") as f:
        json.dump({
            "error": safe_error
        }, f)
    print(f"Error: {safe_error}")