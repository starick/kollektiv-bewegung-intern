export type AuditEvent = 'created' | 'updated' | 'deleted' | 'restored';

export type AuditChange =
  | { attribute: string; summarized: true }
  | { attribute: string; summarized: false; old: unknown; new: unknown };

export interface Audit {
  id: number;
  event: AuditEvent;
  createdAt: string;
  user: { id: number; name: string } | null;
  changes: AuditChange[];
}
