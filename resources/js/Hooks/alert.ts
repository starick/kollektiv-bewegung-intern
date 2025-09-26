import { Dictionary } from '@/Types/dictionary';
import { Message, MessageSeverity } from '@/Types/message';
import { ref } from 'vue';
import { uuid } from 'vue-uuid';

export default function useAlert() {
  const alerts = ref<Dictionary<Message>>({});

  const addMessage = (text: string, severity: MessageSeverity) => {
    alerts.value[uuid.v4()] = { text, severity };
  };

  const removeMessage = (uuid: string | number) => {
    const { [uuid]: _, ...rest } = alerts.value;
    alerts.value = rest;
  };

  return { messages: alerts.value, addMessage, removeMessage };
}
