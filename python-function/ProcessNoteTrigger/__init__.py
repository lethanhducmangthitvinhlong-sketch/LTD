import logging
import json
import azure.functions as func

def main(msg: func.ServiceBusMessage):
    logging.info('Python Service Bus queue trigger function processed a message.')
    
    message_body = msg.get_body().decode('utf-8')
    logging.info(f'Nội dung nhận được từ Queue: {message_body}')
    
    # Chuyển đổi dữ liệu JSON từ message gửi sang
    data = json.loads(message_body)
    note_id = data.get('note_id')
    content = data.get('content')
    
    if note_id is not None:
        # Xử lý logic ngầm (Ví dụ: Gọi Azure AI Language phân tích nội dung hoặc cập nhật trạng thái vào MySQL)
        logging.info(f'Đang xử lý phân tích background cho Note ID: {note_id}')
    else:
        logging.warning('Dữ liệu message không hợp lệ, thiếu note_id.')