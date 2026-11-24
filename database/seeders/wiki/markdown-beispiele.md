Wiki-Seiten und Protokolle werden in **Markdown** geschrieben: normaler Text mit ein paar Zeichen für die Formatierung. Hier steht jeweils zuerst, was du tippst, und darunter, wie es aussieht. Am einfachsten lernst du es, wenn du diese Seite bearbeitest und zwischen **Bearbeiten** und **Vorschau** wechselst.

## Überschriften

```
## Große Überschrift
### Kleinere Überschrift
```

### Kleinere Überschrift

Eine einzelne Raute (`#`) ergibt eine Überschrift so groß wie der Seitentitel. Fang deshalb mit zwei Rauten an.

## Text hervorheben

```
**fett**, *kursiv*, ~~durchgestrichen~~ und `Code`
```

**fett**, *kursiv*, ~~durchgestrichen~~ und `Code`

Ein neuer Absatz beginnt nach einer Leerzeile. Ein einfacher Zeilenumbruch ohne Leerzeile
wird zu einem Leerzeichen.

## Listen

```
- Matten
- Blöcke
  - zwei pro Person

1. Raum aufschließen
2. Heizung an
3. Musik an
```

- Matten
- Blöcke
  - zwei pro Person

1. Raum aufschließen
2. Heizung an
3. Musik an

## Checklisten

```
- [x] Raum gebucht
- [ ] Flyer drucken
```

- [x] Raum gebucht
- [ ] Flyer drucken

Die Kästchen lassen sich nicht anklicken. Zum Abhaken die Seite bearbeiten und `[ ]` durch `[x]` ersetzen.

## Links

```
Mehr dazu unter [Wiki-Seiten]({{wiki:Wiki-Seiten}}).

Oder auf der [Beispiel-Website](https://example.org).

Eine Adresse allein wird auch zum Link: https://example.org
```

Mehr dazu unter [Wiki-Seiten]({{wiki:Wiki-Seiten}}).

Oder auf der [Beispiel-Website](https://example.org).

Eine Adresse allein wird auch zum Link: https://example.org

Für Links innerhalb des Tools reicht der Teil der Adresse ab `/`, z. B. `/wiki/…` oder `/protocols/…`.

## Zitate

```
> Wir probieren das drei Monate aus und sprechen dann wieder darüber.
```

> Wir probieren das drei Monate aus und sprechen dann wieder darüber.

## Tabellen

```
| Tag      | Kurs        | Leitung |
|----------|-------------|---------|
| Montag   | Yoga        | Anna    |
| Mittwoch | Rückenfit   | Ben     |
```

| Tag      | Kurs        | Leitung |
|----------|-------------|---------|
| Montag   | Yoga        | Anna    |
| Mittwoch | Rückenfit   | Ben     |

Die Spalten müssen nicht sauber untereinander stehen, die senkrechten Striche reichen.

## Trennlinie

```
---
```

---

## Code-Blöcke

Text zwischen drei Backticks wird genau so angezeigt, wie er dasteht. Praktisch für Beispiele wie auf dieser Seite:

````
```
Alles hier drin bleibt, wie es ist. **Auch das.**
```
````

```
Alles hier drin bleibt, wie es ist. **Auch das.**
```

## Videos

Ein Link zu YouTube oder Vimeo **allein in einer Zeile** wird zu einem Video. Es lädt erst, wenn jemand darauf klickt.

```
[Das erste YouTube-Video](https://www.youtube.com/watch?v=jNQXAC9IVRw)
```

[Das erste YouTube-Video](https://www.youtube.com/watch?v=jNQXAC9IVRw)

Steht der Link mitten im Text oder in einer Liste, bleibt er ein normaler Link.

## Was nicht geht

- **Bilder** werden nicht angezeigt. Verlinke sie stattdessen, z. B. aus einem geteilten Ordner.
- **HTML** wird als Text angezeigt und nicht ausgeführt.
