import { Dictionary } from '@/Types/dictionary';
import { Severity } from '@/Types/severity';

import { useToast } from 'primevue';

export default function useAlert() {
  const toast = useToast();

  const add = (text: string, severity: Severity) => {
    toast.add({ severity, detail: text });
  };
  const error = (text: string, e?: Event | Dictionary<string>) => {
    console.error(text, e);
    add(text, 'error');
  };

  return { add, error };
}
