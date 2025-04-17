from telethon.sync import TelegramClient
from telethon.sessions import StringSession

# اطلاعات ثابت از اپلیکیشن Telegram
api_id = 26530688
api_hash = '9c5bb5d64b62bdcbd9f66a44ba854ba1'

# شماره تلفن
phone = input("📞 شماره‌ت رو وارد کن (مثلاً +989371436323): ")

with TelegramClient(StringSession(), api_id, api_hash) as client:
    client.start(phone=phone)
    print("\n✅ StringSession ساخته شد:\n")
    print(client.session.save())
