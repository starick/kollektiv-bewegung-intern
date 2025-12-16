import { Dictionary } from '@/Types/dictionary';
import { Severity } from '@/Types/severity';
import { useToast } from 'primevue/usetoast';

export default function useAlert() {
  const toast = useToast();

  const add = (text: string, severity: Severity) => {
    toast.add({ severity, summary: text, life: 3000 });
  };

  const error = (text: string, e?: Event | Dictionary<string>) => {
    console.error(text, e);
    toast.add({ severity: 'error', summary: text, detail: e, life: 3000 });
  };

  return { add, error };
}
