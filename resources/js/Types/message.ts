export type MessageSeverity = 'success' | 'info' | 'warn' | 'error';

export interface Message {
  text: string;
  severity: MessageSeverity;
}
