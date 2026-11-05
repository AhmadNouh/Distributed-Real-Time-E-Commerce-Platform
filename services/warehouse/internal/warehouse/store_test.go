package warehouse

import (
	"testing"
)

func TestMergeItemsCombinesDuplicateLines(t *testing.T) {
	merged, err := mergeItems([]Item{
		{ProductID: 2, Quantity: 1},
		{ProductID: 1, Quantity: 3},
		{ProductID: 2, Quantity: 2},
	})
	if err != nil {
		t.Fatalf("unexpected error: %v", err)
	}

	expected := []Item{
		{ProductID: 1, Quantity: 3},
		{ProductID: 2, Quantity: 3},
	}

	if len(merged) != len(expected) {
		t.Fatalf("expected %d lines, got %d: %+v", len(expected), len(merged), merged)
	}

	for i := range expected {
		if merged[i] != expected[i] {
			t.Fatalf("line %d: expected %+v, got %+v", i, expected[i], merged[i])
		}
	}
}

func TestMergeItemsRejectsInvalidInput(t *testing.T) {
	cases := map[string][]Item{
		"empty":            {},
		"zero product id":  {{ProductID: 0, Quantity: 1}},
		"zero quantity":    {{ProductID: 1, Quantity: 0}},
		"negative qty":     {{ProductID: 1, Quantity: -5}},
		"negative product": {{ProductID: -1, Quantity: 1}},
	}

	for name, items := range cases {
		t.Run(name, func(t *testing.T) {
			if _, err := mergeItems(items); err == nil {
				t.Fatalf("expected an error for %s", name)
			}
		})
	}
}

// The database payload must keep the json tags of reservationRow, otherwise
// Release() would decode zero quantities and never give stock back.
func TestReservationPayloadRoundTrip(t *testing.T) {
	rows := toReservationRows([]Item{
		{ProductID: 5, Quantity: 2},
		{ProductID: 1, Quantity: 1},
	})

	if len(rows) != 2 {
		t.Fatalf("expected 2 rows, got %d", len(rows))
	}

	if rows[0].ProductID != 5 || rows[0].Quantity != 2 {
		t.Fatalf("unexpected first row: %+v", rows[0])
	}

	if rows[1].ProductID != 1 || rows[1].Quantity != 1 {
		t.Fatalf("unexpected second row: %+v", rows[1])
	}
}
