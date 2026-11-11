export type AuditEvent = 'created' | 'updated' | 'deleted' | 'restored';

export interface DiffLine {
  type: 'context' | 'added' | 'removed';
  text: string;
}

export type AuditChange =
  | { attribute: string; type: 'value'; old: unknown; new: unknown }
  | { attribute: string; type: 'diff'; added: number; removed: number; hunks: DiffLine[][] };

export interface Audit {
  id: number;
  event: AuditEvent;
  createdAt: string;
  user: { id: number; name: string } | null;
  changes: AuditChange[];
}
